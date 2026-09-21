# JanogaGo

Custom WordPress theme for JanogaGo's managed food-vending service website.

The theme supports Latvian and English content through Polylang, editable page content, WordPress Media Library images, standard menus, a Custom Logo, and website enquiries stored in WordPress.

## Local theme location

`theme/janogago`

## Future droplet deployment

The `scripts/` folder contains theme and database synchronization scripts prepared for the future JāņogaGO droplet. They are safe to commit because they contain no host, domain, or private-key details.

After creating the droplet, copy `scripts/janogago-droplet.env.example` to `scripts/janogago-droplet.env`, add the real values, and keep that file out of Git. Each script explains the required fields and stops if the droplet details are missing.
