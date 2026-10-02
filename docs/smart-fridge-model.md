# Jāņoga Smart Fridge model

The website model is a visual reproduction of the supplied Jāņoga machine. Dimensions below are the supplier's published Smart Fridge dimensions. They have **not been confirmed for this exact customer variant** and must not be used as installation clearances or manufacturing dimensions.

| Measurement | Millimetres | Blender metres |
| --- | ---: | ---: |
| Height | 1930 | 1.930 |
| Width | 1105 | 1.105 |
| Depth | 760 | 0.760 |

Source: [Vendmaster, Boost Smart Fridge](https://vendmaster.co.uk/product/boost-smart-fridge/). These describe the nominal cabinet. Protruding payment hardware, camera dome, door frame and feet in the prototype can extend its bounding box. Confirm the machine model, dimensions, door opening and service clearances with Boost before planning installation.

The supplied `Boost_Inc_Smart_Fridge_Tehniskā_Specifikācija.pages` describes the Smart Fridge family but gives no dimensioned customer drawing. `SF24 Parts Guide 2.pdf` establishes wrap-panel placement, not cabinet dimensions. The supplied print JPEG provides the actual side branding.

## Files and editing

Editable Blender model:
`/Users/aigarspeda/Desktop/JanogaGo-doc/automati/janoga-smart-fridge.blend`

The reproducible build script is `scripts/build-smart-fridge.py` in this repository. The website GLB is in the Local WordPress Media Library at `/Users/aigarspeda/Local Sites/janogago/app/public/wp-content/uploads/2026/10/janoga-smart-fridge-sealed.glb`.

The temporary standalone preview at `http://127.0.0.1:8766/` was removed and its server stopped on 2026-10-02. Preview the model on the actual local website at `http://janogago.local/`.

The `.blend` packs its images. The `.glb` packs its textures and uses metres. Named front, left and right branding parts remain independently editable. The screen uses a photograph; the rear service panel and vents are inferred.

## Cabinet and wrap seams

The refrigerated compartment has a full-height opaque rear wall, overlapping its side walls, roof and floor. The smaller internal liner and external service panel remain separate details. The contents cannot be seen through the rear. Front and left wrap meshes share their edge coordinates. The front artwork uses an alpha mask with a 0.9 cutoff to discard pale RGB in transparent cut-out pixels; the supplied photo and print artwork remain unchanged. Side UVs stay within the printed panel and use uniform texel density to preserve the circular logo.

Verified with 561 rear-facing visibility rays, front/side edge-coordinate checks, GLB material checks, Blender renders and browser rotation. The corrected model has 41,921 triangles and is about 4.16 MB.

## Stocked interior

The cabinet is hollow, with a recessed rear liner and six full-depth shelf decks. There are **149 individually editable 3D packaging volumes**, including bottles, cans, domed cups, meal trays, paper bags and snack packs, with three rows of stock on most shelves. The previous single full-door product-photo plane has been removed.

The front faces and visible meal lids use UV projections from the supplied machine photo. Containers have physical depth, and round drink/cup silhouettes are actual mesh geometry. Package profiles, rear stock rows and side/back packaging colors are approximations. These are photo-textured packaging models, not scanned individual food ingredients or production product renders.

In Blender, expand **Contents | editable stock**. Product names include shelf, item and row, so they can be moved, replaced or removed individually. Rebuild with `scripts/build-smart-fridge.py` through Blender's Python runner, passing `--output`, `--photo` and `--wrap` after `--`. No external Python packages are needed. Only the machine and contents are exported to GLB; the studio is kept in the `.blend`.

Both languages select `janoga-smart-fridge-sealed.glb`, Media Library attachment **204** locally and **142** on the droplet. The earlier stocked model, attachment 201, and flat-interior model, attachment 198, remain available for rollback. The nominal cabinet dimensions and side-logo UV scale are unchanged.

The left wrap's narrow artwork was previously widened to fill the side. It now uses equal horizontal and vertical texel density, cropping its patterned background vertically. The logo's circle retains its proportions. The right wrap also uses uniform texel density. This is a web visual, not a print-production UV template.

On WordPress, select the existing hero Image block. The sidebar's **3D machine / 3D automāts** panel selects or replaces its GLB from the Media Library. Remove 3D to restore the normal image. Edit the rotation button's accessible labels there. The original native Image block supplies the photo backup and alt text. The optional preview is another Media Library image referenced by `jgModelPosterId`. It is exported from the same model-viewer renderer, at `0deg 90deg 4.74m`, target `0m .965m 0m`, 30° field of view, neutral tone mapping and exposure 1, using `toBlob({mimeType: "image/png", idealAspect: true})`. Both the transparent margins and the camera settings must match the viewer. Regenerate the preview after changing the model, camera or lighting. Replacing the GLB in Gutenberg clears the preview automatically. Deleting it removes both the image and model.

The theme owns display and motion only. The viewer is centered within its hero area and rotates around the nominal cabinet midpoint `0m .965m 0m`. Panning, tap-to-recenter and zoom are disabled so clicking or dragging cannot move the model out of frame. The initial frontal camera is `0deg 90deg 4.74m`. The matching preview stays visible behind the model during its 320 ms fade-in, avoiding a brightness dip; it hides once the model is opaque, then rotation starts. Without a selected preview the original photo uses the usual crossfade. A preview image or model error restores the original photo attributes and framing. Reduced motion skips the fade and autoplay; errors cancel the reveal and restore the photo. A slow 32-second front-facing turn runs while visible, stops for direct interaction and reduced-motion preferences, and has an icon-only pause/play button at the upper-right of the model area. Its 44px target has translated accessible labels, hover titles and keyboard focus styling. The visible drag hint was removed; its saved block attribute remains registered for compatibility. The local viewer library is Google model-viewer 4.2.0, licensed under Apache 2.0. Its code is vendored in the theme; content images and GLBs stay in WordPress uploads.

The homepage content-sync script exports/imports GLB media and remaps `jgModelId` and `jgModelPosterId` alongside the original image ID. It validates the GLB header and checksum, transfers dimension metadata and reuses matching attachments. Theme 1.0.74, the model and matching preview are deployed to both live languages.
