import bpy, math, argparse, sys
from pathlib import Path
from mathutils import Vector

parser=argparse.ArgumentParser(description='Build the photo-textured Jāņoga Smart Fridge in Blender.')
parser.add_argument('--output',required=True,type=Path)
parser.add_argument('--photo',required=True,type=Path)
parser.add_argument('--wrap',required=True,type=Path)
args=parser.parse_args(sys.argv[sys.argv.index('--')+1:] if '--' in sys.argv else [])
OUT,PHOTO=args.output,args.photo
OUT.mkdir(parents=True,exist_ok=True)
bpy.ops.object.select_all(action='SELECT');bpy.ops.object.delete(use_global=False)
for c in list(bpy.data.collections):
 if c.name!='Collection':bpy.data.collections.remove(c)
base=bpy.data.collections.get('Collection');base.name='01 Machine'
scene=bpy.context.scene
scene.unit_settings.system='METRIC'
scene.unit_settings.length_unit='MILLIMETERS'
W,H,D=1.105,1.930,.760
LX,RX,TY,BY=88,982,5,1467
PW,PH=1064,1478
FY=-D/2

def X(px):return (px-LX)/(RX-LX)*W-W/2

def Z(py):return (BY-py)/(BY-TY)*H

def UV(px,py):return (px/PW,1-py/PH)

root=bpy.data.objects.new('JANOGA Smart Fridge | approximate 1930 x 1105 x 760 mm',None);base.objects.link(root)
root['prototype']=True
root['dimensions_source']='https://vendmaster.co.uk/product/boost-smart-fridge/'
root['dimension_status']='Published supplier size; exact customer variant unconfirmed'
root['interior']='Six recessed shelves with approximate 3D packages, trays, bottles and cans; photo-projected textures, not scanned food'
root['branding']='Supplied SF24 print artwork for side wraps; front reference photo for fascia and contents'
root['parts_guide']='SF24 Parts Guide 2.pdf: panel placement reference, no cabinet dimensions'
img=bpy.data.images.load(str(PHOTO));img.pack()
img.name='Jāņoga original front reference (packed)'

def mat(name,color,metal=0,rough=.4):
 m=bpy.data.materials.new(name);m.use_nodes=True
 p=m.node_tree.nodes.get('Principled BSDF');p.inputs['Base Color'].default_value=(*color,1);p.inputs['Metallic'].default_value=metal;p.inputs['Roughness'].default_value=rough
 m.diffuse_color=(*color,1);return m

black=mat('Cabinet | satin charcoal',(.009,.011,.013),0,.85)
black.node_tree.nodes.get('Principled BSDF').inputs['Specular IOR Level'].default_value=.03
trim=mat('Door | black aluminium',(.007,.009,.01),.7,.25)
steel=mat('Shelf rails | stainless steel',(.37,.40,.42),.85,.24)
camera_mat=mat('Camera dome | smoked acrylic',(.009,.013,.018),.32,.10)
rubber=mat('Feet | rubber',(.008,.009,.01),0,.9)

def photo_mat(name,emission=0):
 m=mat(name,(1,1,1),0,.57)
 p=m.node_tree.nodes.get('Principled BSDF');p.inputs['Specular IOR Level'].default_value=.08;p.inputs['Roughness'].default_value=.82;t=m.node_tree.nodes.new('ShaderNodeTexImage');t.image=img;t.interpolation='Linear';t.label='Replace with approved artwork or product photography'
 m.node_tree.links.new(t.outputs['Color'],p.inputs['Base Color'])
 if emission:
  m.node_tree.links.new(t.outputs['Color'],p.inputs['Emission Color']);p.inputs['Emission Strength'].default_value=emission
 return m
front_art=photo_mat('Brand Front Artwork | replaceable UV panel',.05)
# The cut-out photo contains pale RGB in nearly transparent edge pixels.
# Export an alpha mask so those pixels cannot become white specks on the fascia.
front_texture=next(node for node in front_art.node_tree.nodes if node.type=='TEX_IMAGE')
front_alpha=front_art.node_tree.nodes.new('ShaderNodeMath');front_alpha.operation='GREATER_THAN';front_alpha.inputs[1].default_value=.9
front_alpha.label='Discard transparent cut-out fringe'
front_art.node_tree.links.new(front_texture.outputs['Alpha'],front_alpha.inputs[0])
front_art.node_tree.links.new(front_alpha.outputs[0],front_art.node_tree.nodes.get('Principled BSDF').inputs['Alpha'])
wrap_img=bpy.data.images.load(str(args.wrap));wrap_img.pack();wrap_img.name='Jāņoga supplied SF24 print artwork (packed)'
side_art=mat('Brand Side Pattern | supplied SF24 wrap artwork',(1,1,1),0,.75)
p=side_art.node_tree.nodes.get('Principled BSDF');p.inputs['Specular IOR Level'].default_value=.06
t=side_art.node_tree.nodes.new('ShaderNodeTexImage');t.image=wrap_img;t.interpolation='Linear'
side_art.node_tree.links.new(t.outputs['Color'],p.inputs['Base Color'])
p.inputs['Emission Strength'].default_value=0
def WUV(px,py):return (px/1386,1-py/1180)
products=photo_mat('Contents | photo-projected packaging',.20)
screen=photo_mat('Touchscreen | replaceable screen image',.3)
terminal_art=photo_mat('Payment Terminal | reference artwork',.08)
led=mat('LED | cool white',(.63,.78,1),0,.35)
led.node_tree.nodes.get('Principled BSDF').inputs['Emission Color'].default_value=(.63,.78,1,1)
led.node_tree.nodes.get('Principled BSDF').inputs['Emission Strength'].default_value=2

def register(obj,material=None):
 obj.parent=root
 if material:obj.data.materials.append(material)
 obj['part']=obj.name
 return obj

def box(name,loc,size,material,bevel=.003):
 bpy.ops.mesh.primitive_cube_add(size=1,location=loc);o=bpy.context.object;o.name=name;o.dimensions=size
 bpy.ops.object.transform_apply(location=False,rotation=False,scale=True)
 register(o,material)
 if bevel:
  b=o.modifiers.new('Soft manufactured edges','BEVEL');b.width=bevel;b.segments=3
  n=o.modifiers.new('Weighted normals','WEIGHTED_NORMAL')
 return o

def rectangle(name,px0,py0,px1,py1,y,material):
 verts=[(X(px0),y,Z(py1)),(X(px1),y,Z(py1)),(X(px1),y,Z(py0)),(X(px0),y,Z(py0))]
 mesh=bpy.data.meshes.new(name);mesh.from_pydata(verts,[],[(0,1,2,3)]);mesh.update()
 o=bpy.data.objects.new(name,mesh);base.objects.link(o);register(o,material)
 uv=mesh.uv_layers.new(name='Brand UV')
 for l,(px,py) in zip(mesh.loops,[(px0,py1),(px1,py1),(px1,py0),(px0,py0)]):uv.data[l.index].uv=UV(px,py)
 return o

def front_box(name,x0,y0,x1,y1,y,depth,material,bevel=.004):
 return box(name,((X(x0)+X(x1))/2,y,(Z(y0)+Z(y1))/2),(X(x1)-X(x0),depth,Z(y0)-Z(y1)),material,bevel)

# Refrigerated cabinet; named outer panels retain independent materials.
main_x0,main_x1=X(383),X(982)
# A hollow cabinet allows shelves and stock to occupy its real depth.
body=box('Refrigerated cabinet | left wall',(main_x0+.012,0,H/2),(.024,D-.02,H),black,.004)
box('Refrigerated cabinet | right wall',(main_x1-.012,0,H/2),(.024,D-.02,H),black,.004)
box('Refrigerated cabinet | roof',((main_x0+main_x1)/2,0,H-.012),(main_x1-main_x0,D-.02,.024),black,.004)
box('Refrigerated cabinet | floor',((main_x0+main_x1)/2,0,.045),(main_x1-main_x0,D-.02,.090),black,.004)
# Full-height opaque rear skin overlaps the walls, roof and floor. The recessed
# liner and service hatch are details, not substitutes for a sealed cabinet.
box('Rear | full-height insulated cabinet wall',((main_x0+main_x1)/2,D/2-.014,H/2),(main_x1-main_x0,.028,H),black,.003)
liner=mat('Interior | graphite liner',(.026,.029,.033),0,.85)
liner.node_tree.nodes.get('Principled BSDF').inputs['Specular IOR Level'].default_value=.03
box('Interior | rear liner',((X(421)+X(938))/2,.345,(Z(104)+Z(1376))/2),(X(938)-X(421),.024,Z(104)-Z(1376)),liner,.002)

# Smooth control fascia outline based on the supplied photo.
profile=[(382,5),(155,5),(134,8),(116,17),(102,31),(92,50),(88,73),(88,892),(90,923),(99,946),(116,964),(169,1007),(196,1037),(212,1064),(218,1095),(218,1398),(223,1422),(238,1444),(259,1459),(280,1467),(382,1467)]
verts=[(X(p[0]),y,Z(p[1])) for y in [FY-.005,D/2-.015] for p in profile]
n=len(profile);faces=[tuple(range(n-1,-1,-1)),tuple(range(n,2*n))]
faces.extend((i,(i+1)%n,(i+1)%n+n,i+n) for i in range(n))
mesh=bpy.data.meshes.new('Curved control housing');mesh.from_pydata(verts,[],faces);mesh.update()
housing=bpy.data.objects.new('Control housing | curved silhouette',mesh);base.objects.link(housing);register(housing,black)
bev=housing.modifiers.new('Rounded housing edge','BEVEL');bev.width=.004;bev.segments=3
housing.modifiers.new('Weighted normals','WEIGHTED_NORMAL')
# Brand fascia remains a separate replaceable piece, with normalized full-photo UVs.
def fascia_x(px):return X(px)-(.002 if px!=382 else 0)
mesh=bpy.data.meshes.new('Front brand panel');mesh.from_pydata([(fascia_x(a),FY-.007,Z(b)) for a,b in profile],[],[tuple(range(n))]);mesh.update()
fascia=bpy.data.objects.new('Brand Front | curved artwork panel',mesh);base.objects.link(fascia);register(fascia,front_art)
uv=mesh.uv_layers.new(name='Brand UV')
for loop in mesh.loops:uv.data[loop.index].uv=UV(*profile[loop.vertex_index])
fascia['replaceable_artwork']=True
fascia['source_template']='Original full image 1064 x 1478; coordinates retained'

# Supplied flat print sheet: independently mapped left and right side artwork.
def side_panel(name,x,y0,y1,z0,z1):
 mesh=bpy.data.meshes.new(name)
 mesh.from_pydata([(x,y0,z0),(x,y1,z0),(x,y1,z1),(x,y0,z1)],[],[(0,1,2,3)]);mesh.update()
 o=bpy.data.objects.new(name,mesh);base.objects.link(o);register(o,side_art)
 uv=mesh.uv_layers.new(name='SF24 right panel UV')
 # Uniform texel density keeps the round logo round in model space.
 span=(z1-z0)*299/(y1-y0)
 for loop,xy in zip(mesh.loops,[(1039,156+span),(1338,156+span),(1338,156),(1039,156)]):uv.data[loop.index].uv=WUV(*xy)
 o['replaceable_artwork']=True;o['print_panel']='D | right-hand side'
 return o
side_panel('Brand Right | supplied circle-logo wrap',main_x1+.002,FY+.023,D/2-.025,.026,H-.014)
# Left silhouette follows the curved housing; depth and height map the complete tall print panel.
verts=[];faces=[];coords=[]
for i in range(1,n-1):
 a,b=profile[i],profile[i+1]
 if a[0]==382 and b[0]==382:continue
 j=len(verts)
 for point,y,u in [(a,FY-.007,234),(a,D/2-.025,51),(b,D/2-.025,51),(b,FY-.007,234)]:
  # Share the front fascia boundary rather than leaving a floating strip.
  verts.append((fascia_x(point[0]),y,Z(point[1])))
  # The narrow control-side artwork must be cropped vertically, not widened.
  # Stay inside the printed artwork and retain equal scale in both axes.
  scale=183/((D/2-.025)-(FY-.007))
  coords.append((u,180+(Z(TY)-Z(point[1]))*scale))
 faces.append((j,j+1,j+2,j+3))
mesh=bpy.data.meshes.new('Left supplied wrap');mesh.from_pydata(verts,[],faces);mesh.update()
o=bpy.data.objects.new('Brand Left | supplied circle-logo wrap',mesh);base.objects.link(o);register(o,side_art)
uv=mesh.uv_layers.new(name='SF24 left panel UV')
for loop in mesh.loops:uv.data[loop.index].uv=WUV(*coords[loop.vertex_index])
o['replaceable_artwork']=True;o['print_panel']='C | control side artwork, aspect-preserving vertical crop'
o['logo_aspect']='Uniform texel density: 183 pixels across the physical side depth in both axes'

# Top and bottom red trims are independently replaceable.
front_box('Brand Top | header trim',385,5,982,63,FY-.005,.025,black,.003)
rectangle('Brand Top | artwork',386,7,980,61,FY-.020,front_art)
front_box('Brand Bottom | plinth trim',385,1413,982,1467,FY-.005,.025,black,.003)
rectangle('Brand Bottom | artwork',386,1415,980,1465,FY-.020,front_art)

# A genuinely extruded door frame surrounding a recessed photographic interior.
front_box('Door frame | left upright',385,66,421,1413,FY-.032,.045,trim,.005)
front_box('Door frame | right upright',938,66,982,1413,FY-.032,.045,trim,.005)
front_box('Door frame | upper rail',420,66,939,104,FY-.032,.045,trim,.005)
front_box('Door frame | lower rail',420,1376,939,1413,FY-.032,.045,trim,.005)
glass=mat('Door Glass | clear protective pane',(.82,.92,1),0,.14)
glass.node_tree.nodes.get('Principled BSDF').inputs['Alpha'].default_value=.012
glass.diffuse_color=(.82,.92,1,.012)
rectangle('Door Glass | separate clear pane',421,104,938,1376,FY-.026,glass)
# Edge lighting sits physically in front of the interior.
front_box('LED | left vertical',424,114,427,1364,FY-.019,.005,led,.001)
front_box('LED | right vertical',930,114,933,1364,FY-.019,.005,led,.001)

# Shelf decks and rails occupy the cabinet cavity.
deck=mat('Interior | shelf decks',(.085,.090,.10),.35,.58)
for i,py in enumerate([284,506,695,904,1090,1364],1):
 box('Shelf %02d | full-depth deck'%i,((X(433)+X(926))/2,.005,Z(py+4)),(X(926)-X(433),.60,.008),deck,.002)
 front_box('Shelf %02d | front rail'%i,433,py,926,py+4,FY+.040,.011,steel,.002)
 front_box('Shelf %02d | retaining lip'%i,433,py-10,926,py-7,FY+.033,.006,steel,.001)

# Photo-projected packaging on real, closed product volumes. No full-door image plane.
contents_root=bpy.data.objects.new('Contents | editable stock',None);base.objects.link(contents_root);contents_root.parent=root
contents_root['construction']='Approximate packaging volumes textured from the supplied front photo; rear rows are inferred'
pack_colors={
 'kraft':(.37,.25,.14),'red':(.32,.018,.032),'yellow':(.62,.35,.025),
 'cream':(.54,.48,.35),'green':(.18,.25,.08),'blue':(.02,.13,.36),
 'pink':(.53,.12,.22),'black':(.013,.016,.02),'orange':(.65,.18,.018)
}
pack_mats={k:mat('Contents | '+k+' packaging',v,0,.65) for k,v in pack_colors.items()}
for m in pack_mats.values():m.node_tree.nodes.get('Principled BSDF').inputs['Specular IOR Level'].default_value=.08

def pack(name,bounds,front,depth,color='kraft',kind='bag'):
 x0,y0,x1,y1=bounds;dx=x1-x0;dy=y1-y0
 # Chamfered outlines omit photo background at the corners of packs and meal trays.
 c=.08 if kind=='bag' else .13
 pts=[(x0+dx*c,y0),(x1-dx*c,y0),(x1,y0+dy*c),(x1,y1-dy*c),
      (x1-dx*c,y1),(x0+dx*c,y1),(x0,y1-dy*c),(x0,y0+dy*c)]
 verts=[(X(x),y,Z(z)) for y in [front,front+depth] for x,z in pts];n=len(pts)
 faces=[tuple(range(n-1,-1,-1)),tuple(range(n,2*n))]+[(i,(i+1)%n,(i+1)%n+n,i+n) for i in range(n)]
 mesh=bpy.data.meshes.new(name);mesh.from_pydata(verts,[],faces);mesh.update()
 o=bpy.data.objects.new(name,mesh);base.objects.link(o);register(o,products);o.parent=contents_root
 mesh.materials.append(pack_mats[color])
 for p in mesh.polygons:p.material_index=0 if p.index==0 else 1
 uv=mesh.uv_layers.new(name='Photo projection')
 for loop in mesh.loops:uv.data[loop.index].uv=UV(*pts[loop.vertex_index%n])
 # Meal lids show the photographed food from above as the cabinet turns.
 if kind=='tray':
  for poly in mesh.polygons:
   if poly.normal.z>.65:
    poly.material_index=0
    for li in poly.loop_indices:
     vi=mesh.loops[li].vertex_index
     uv.data[li].uv=UV(pts[vi%n][0],y0+dy*(.48 if vi<n else .08))
 b=o.modifiers.new('Packaging edges','BEVEL');b.width=.0015;b.segments=2
 o.modifiers.new('Packaging normals','WEIGHTED_NORMAL')
 o['contents_type']=kind;o['texture_source']='Supplied front photo, projected onto packaging';return o

def round_pack(name,bounds,front,depth,color='cream',kind='cup'):
 x0,y0,x1,y1=bounds;cx=(X(x0)+X(x1))/2;rx=(X(x1)-X(x0))/2
 height=Z(y0)-Z(y1);bottom=Z(y1);ry=depth/2;cy=front+ry
 if kind=='bottle':profile=[(0,.78),(.04,.94),(.67,1),(.77,.84),(.88,.33),(.98,.29),(1,.29)]
 elif kind=='can':profile=[(0,.88),(.025,1),(.96,1),(.985,.89),(1,.89)]
 else:profile=[(0,.68),(.08,.88),(.45,1),(.60,1),(.81,.83),(.94,.52),(1,.10)]
 seg=24;verts=[];faces=[]
 for z,r in profile:
  for j in range(seg):
   angle=2*math.pi*j/seg;verts.append((cx+rx*r*math.cos(angle),cy+ry*r*math.sin(angle),bottom+height*z))
 for i in range(len(profile)-1):
  for j in range(seg):a=i*seg+j;b=i*seg+(j+1)%seg;faces.append((a,b,b+seg,a+seg))
 faces.extend([tuple(range(seg-1,-1,-1)),tuple((len(profile)-1)*seg+j for j in range(seg))])
 mesh=bpy.data.meshes.new(name);mesh.from_pydata(verts,[],faces);mesh.update()
 o=bpy.data.objects.new(name,mesh);base.objects.link(o);register(o,products);o.parent=contents_root
 mesh.materials.append(pack_mats[color]);mesh.materials.append(steel)
 for poly in mesh.polygons:
  poly.material_index=0 if poly.normal.y<-.12 else 1
  if kind=='can' and poly.index>=len(faces)-1:poly.material_index=2
  poly.use_smooth=poly.index<len(faces)-2
 uv=mesh.uv_layers.new(name='Photo projection')
 for loop in mesh.loops:
  v=mesh.vertices[loop.vertex_index].co
  px=x0+(v.x-X(x0))/(X(x1)-X(x0))*(x1-x0)
  py=y1-(v.z-bottom)/height*(y1-y0)
  uv.data[loop.index].uv=UV(px,py)
 o['contents_type']=kind;o['texture_source']='Photo-projected front, approximate packaging profile';return o

# Shelf 1: individually extruded snack packs.
snacks=[(451,174,482,271,'red'),(485,180,515,271,'cream'),(516,141,549,271,'red'),
 (553,127,589,271,'red'),(590,170,625,271,'black'),(632,147,667,271,'yellow'),
 (669,151,695,271,'yellow'),(699,168,744,271,'cream'),(746,162,802,271,'kraft'),
 (806,179,859,271,'cream'),(863,169,913,271,'red')]
for i,item in enumerate(snacks):
 for row in range(3):pack(f'Contents | 1 Snack {i+1:02d} row {row+1}',item[:4],FY+.068+row*.145,.12,item[4])
# The refrigeration sticker belongs to the glass, not the food.
rectangle('Door Glass | original refrigeration sticker',428,116,486,172,FY-.028,front_art)

# Shelf 2: paper bags, clear bags and stacked black food pots.
bags=[(435,355,492,497),(494,350,547,497),(550,374,611,497),(613,370,688,497),(691,376,813,497)]
for i,bounds in enumerate(bags):
 for row in range(3):pack(f'Contents | 2 Bag {i+1} row {row+1}',bounds,FY+.070+row*.15,.13,'kraft' if i<4 else 'cream')
for row in range(3):
 for j,bounds in enumerate([(816,382,915,431),(816,437,915,494)]):
  round_pack(f'Contents | 2 Food pot {j+1} row {row+1}',bounds,FY+.068+row*.15,.13,'black','cup')

# Shelf 3: salad packs and domed meal/dessert cups.
for row in range(3):
 for j,bounds in enumerate([(437,585,552,631),(437,636,552,683)]):
  pack(f'Contents | 3 Salad tray {j+1} row {row+1}',bounds,FY+.064+row*.16,.14,'cream','tray')
 for j,bounds in enumerate([(554,576,627,678),(629,576,703,678),(705,576,780,678),(782,577,852,678),(852,577,916,678)]):
  round_pack(f'Contents | 3 Domed cup {j+1} row {row+1}',bounds,FY+.074+row*.15,.12,'cream')

# Shelf 4: three visible stacks of meal trays, plus trays behind them.
stacks=[(435,580,[790,825,861,895]),(584,750,[778,817,857,895]),(746,916,[785,825,862,895])]
for row in range(3):
 for col,(x0,x1,ys) in enumerate(stacks):
  for level in range(3):pack(f'Contents | 4 Meal stack {col+1} tray {level+1} row {row+1}',(x0,ys[level],x1,ys[level+1]),FY+.070+row*.16,.14,'cream','tray')
for j,bounds in enumerate([(527,735,691,790),(684,740,884,790)]):
 pack(f'Contents | 4 Rear upper meal {j+1}',bounds,FY+.270,.17,'green','tray')

# Shelf 5: salad boxes and bakery/snack bags.
for row in range(3):
 for j,bounds in enumerate([(437,985,573,1031),(437,1035,573,1080)]):
  pack(f'Contents | 5 Salad tray {j+1} row {row+1}',bounds,FY+.068+row*.16,.14,'cream','tray')
 for j,bounds in enumerate([(579,956,668,1081),(668,969,758,1081),(765,955,831,1081),(831,958,913,1081)]):
  pack(f'Contents | 5 Bakery bag {j+1} row {row+1}',bounds,FY+.077+row*.15,.13,'pink' if j==1 else 'kraft')

# Shelf 6: actual round bottle/can volumes. Rear rows complete the stocked depth.
drinks=[((435,1143,486,1334),'red','bottle'),((487,1180,544,1334),'blue','bottle'),
 ((548,1203,587,1334),'orange','can'),((590,1207,630,1334),'blue','can'),
 ((633,1202,681,1334),'blue','can'),((684,1210,730,1334),'pink','can'),
 ((735,1208,780,1334),'blue','can'),((786,1178,839,1334),'black','can'),
 ((843,1176,908,1334),'blue','bottle')]
for row in range(3):
 for j,(bounds,color,kind) in enumerate(drinks):
  if row and kind=='can':bounds=(bounds[0],bounds[1]-35,bounds[2],bounds[3])
  if row and kind=='bottle':bounds=(bounds[0],bounds[1]-20,bounds[2],bounds[3])
  round_pack(f'Contents | 6 Drink {j+1:02d} row {row+1}',bounds,FY+.065+row*.155,.065,color,kind)

# Touchscreen and terminal protrude from the fascia.
front_box('Touchscreen | black bezel',104,176,379,646,FY-.019,.025,trim,.012)
rectangle('Touchscreen | display',121,198,361,624,FY-.034,screen)
front_box('Payment | terminal housing',142,745,225,851,FY-.029,.033,trim,.006)
rectangle('Payment | terminal face',145,748,221,846,FY-.047,terminal_art)
front_box('Scanner | stainless border',155,680,210,724,FY-.026,.019,steel,.005)
rectangle('Scanner | reader face',158,684,208,720,FY-.037,terminal_art)

# Dome and camera mounting ring are real geometry, not a flat photograph.
bpy.ops.mesh.primitive_cylinder_add(vertices=48,radius=.059,depth=.008,location=(X(331),FY-.023,Z(54)),rotation=(math.pi/2,0,0));o=bpy.context.object;o.name='Camera | mounting ring';register(o,trim)
bpy.ops.mesh.primitive_uv_sphere_add(segments=48,ring_count=24,radius=.055,location=(X(331),FY-.027,Z(54)));o=bpy.context.object;o.name='Camera | smoked dome';o.scale=(1,.53,1);register(o,camera_mat)
for p in o.data.polygons:p.use_smooth=True

# Rear service panel and ventilation, inferred from typical cabinet construction.
box('Rear | service panel',((main_x0+main_x1)/2,D/2+.002,.26),(.54,.011,.43),black,.004)
for i in range(7):box('Rear vent %02d'%i,((main_x0+main_x1)/2,D/2+.009,.12+i*.032),(.40,.008,.008),trim,.001)
for x in [X(292),X(935)]:
 for y in [FY+.12,D/2-.12]:
  bpy.ops.mesh.primitive_cylinder_add(vertices=16,radius=.022,depth=.018,location=(x,y,.009));o=bpy.context.object;o.name='Levelling foot';register(o,rubber)

# Reference photos in an optional, disabled collection aid future edits.
refs=bpy.data.collections.new('02 References (hidden)');scene.collection.children.link(refs)
reference=bpy.data.objects.new('Original front reference | supplied photograph',None);reference.empty_display_type='IMAGE';reference.data=img;reference.empty_display_size=H;refs.objects.link(reference);reference.location=(0,.5,H/2);reference.rotation_euler=(math.pi/2,0,0);reference.hide_viewport=True;reference.hide_render=True

# Presentation scene: calm, neutral lighting and a cream ground.
studio=bpy.data.collections.new('03 Studio (excluded from GLB)');scene.collection.children.link(studio)

def studio_obj(o):
 for c in list(o.users_collection):c.objects.unlink(o)
 studio.objects.link(o);return o
floor_mat=mat('Studio | warm paper',(.90,.87,.81),0,.78)
bpy.ops.mesh.primitive_plane_add(size=200,location=(0,0,-.012));o=bpy.context.object;o.name='Studio ground';o.data.materials.append(floor_mat);o.is_shadow_catcher=True;studio_obj(o)

def aim(o,point):o.rotation_euler=(Vector(point)-o.location).to_track_quat('-Z','Y').to_euler()

def area(name,loc,power,size,color):
 bpy.ops.object.light_add(type='AREA',location=loc);o=bpy.context.object;o.name=name;o.data.energy=power;o.data.shape='DISK';o.data.size=size;o.data.color=color;aim(o,(0,0,1));studio_obj(o)
area('Key | large softbox',(-3,-4,4.5),260,4,(1,.96,.91))
area('Fill | front', (2,-4,2.2),180,3,(.90,.95,1))
area('Rim | back', (2,2,3.7),300,3,(1,.95,.90))
scene.world.color=(.25,.25,.25)
bpy.ops.object.camera_add(location=(-2.8,-6.5,2.50));camera=bpy.context.object;camera.name='Hero three-quarter camera';camera.data.type='ORTHO';camera.data.ortho_scale=2.75;aim(camera,(0,0,.96));studio_obj(camera);scene.camera=camera
scene.render.engine='CYCLES';scene.cycles.device='CPU';scene.cycles.samples=24;scene.cycles.use_denoising=True
scene.render.resolution_x=1100;scene.render.resolution_y=1200;scene.render.resolution_percentage=100
scene.render.image_settings.file_format='PNG';scene.render.image_settings.color_mode='RGBA'
scene.render.film_transparent=True
scene.view_settings.view_transform='Standard';scene.view_settings.look='None';scene.view_settings.exposure=-.45
scene.render.fps=24
scene.frame_start=1;scene.frame_end=192
# Website viewer animates the turn; export geometry stays in its neutral pose.
scene.frame_set(1);root.rotation_euler[2]=0
# Set a useful opening view in Blender.
for screen_data in bpy.data.screens:
 for area_data in screen_data.areas:
  if area_data.type=='VIEW_3D':
   space=area_data.spaces.active;space.region_3d.view_perspective='CAMERA';space.shading.type='MATERIAL'
# Build notes travel with the editable model.
notes=bpy.data.texts.new('READ ME | Prototype limits and editing')
notes.write('JĀŅOGA SMART FRIDGE PROTOTYPE\n\nApproximate size: H1930 W1105 D760 mm. Supplier dimensions are provisional for this exact customer variant.\nFront silhouette, display and branding follow the supplied front photograph. Cabinet depth and rear construction are approximations.\nThe hollow interior has six shelf decks and individually editable packaging volumes, including bottles, cans, domed cups and meal trays. Their front textures are projected from the supplied photograph. Side/back packaging and rear stock rows are approximate, not scanned food.\nBoth original images are packed into this .blend.\n\nEDITING\nBrand Front / Brand Left / Brand Right parts have independent materials. Both side logos retain uniform UV scale. Production UVs need checking against full-resolution print files.\nContents | editable stock contains the named product volumes grouped by shelf, item and row.\nTouchscreen, glass frame, camera dome and terminal are separate objects.\n03 Studio is excluded from GLB. The GLB has no baked animation; the website controls rotation.\n')
# Export only the cabinet; native .blend keeps studio, textures and editable named parts.
bpy.ops.object.select_all(action='DESELECT')
for o in list(base.objects):
 if o.type in {'MESH','EMPTY'}:o.select_set(True)
bpy.context.view_layer.objects.active=body
bpy.ops.export_scene.gltf(filepath=str(OUT/'janoga-smart-fridge.glb'),export_format='GLB',use_selection=True,export_apply=True,export_animations=False,export_extras=True,export_materials='EXPORT',export_yup=True)
bpy.ops.wm.save_as_mainfile(filepath=str(OUT/'janoga-smart-fridge.blend'))
# Save two preview angles; no changes to the existing website.
scene.render.filepath=str(OUT/'preview-three-quarter.png');bpy.ops.render.render(write_still=True)
camera.location=(0,-7,1.3);camera.data.ortho_scale=2.42;aim(camera,(0,0,.96))
scene.render.resolution_x=950;scene.render.resolution_y=1200
scene.render.filepath=str(OUT/'preview-front.png');bpy.ops.render.render(write_still=True)
print('DELIVERED',OUT)

camera.location=(-7,0,H/2);camera.data.ortho_scale=2.42;aim(camera,(0,0,H/2));scene.render.resolution_x=950;scene.render.filepath=str(OUT/'preview-left.png');bpy.ops.render.render(write_still=True)
camera.location=(7,0,H/2);aim(camera,(0,0,H/2));scene.render.filepath=str(OUT/'preview-right.png');bpy.ops.render.render(write_still=True)
camera.location=(0,7,H/2);aim(camera,(0,0,H/2));scene.render.filepath=str(OUT/'preview-back.png');bpy.ops.render.render(write_still=True)
camera.location=(-3,6,2.5);aim(camera,(0,0,H/2));scene.render.filepath=str(OUT/'preview-back-three-quarter.png');bpy.ops.render.render(write_still=True)
