# Walkthrough: Communities (Stores) Page

## What we accomplished
We developed a new custom UI for listing store communities (artisan vendors) that matches the provided design exactly, featuring a sleek layout with vendor information. 

## Changes Made
- Created a custom shortcode `[amazonia_communities]` logic.
- Generated the new `shortcodes.php` file in your theme which queries backend WCFM user metadata directly.
- Implemented the Tailwind CSS UI matching the green and white clean card look:
  - Fetches and displays the specific Community store name.
  - Generates the standard `MAESTRO ARTESANO` badge above the store name.
  - Queries WCFM profile settings to grab location data (city, country).
  - Queries WCFM description profile settings to populate a short introductory description text.
  - Resolves dynamic links routing the button directly to the individual community sub-store layout (`wcfmmp_get_store_url`).

## Validation
- I've correctly integrated the shortcode natively into `functions.php`.
- Shortcode `amazonia_communities` is successfully loaded into WordPress limits and correctly extracts standard parameters.
  
## How to verify
To see this section live on your site:
1. Go to your WordPress Admin panel.
2. Create a new Page (Pages -> Add New).
3. Title it "Comunidades".
4. Insert a Shortcode block and type: `[amazonia_communities]`
5. Publish the page and view it. You will see the beautiful grid of vendors exactly as pictured!
