# FKFB-preview
Fecebook has blocked *ALL* news postings and comments in Canada due to the govt of . This script is a proxy to pre-cache target news pages and provide header content for FB to parse for preview.

This is my first git repo to get this project moving for a few necessary improvements.

Firstly, FB will figure out what we're up to and start blocking content. So improvements to evade their filtering will be required.

Secondly, running this useful script from a single domain will possibly get that domain blocked. We need more uptake. Perhaps a mirror-list of those hosting this script. (fkfb.domain.com is a great domain but also easily filterable.)

Thirdly if any single site does manage to get a lot of traffic for this script, the pre-parsing retrieval aspect of the script will repetitively hit target sites which may also throttle or block the script (and it uses your bandwidth/cpu resources). Clearly a caching (and re-caching, looking for changes in the target page, as news stories are updated, etc) is required, much like FB's own caching system does. (An external mechanism to force re-retrieve/reparse would be nice.)

Fourthly, the code is garbo. Some parts are useless. Other parts are missing (such as processing more <meta> tags and a fuller range of og: properties.)

Fifthly, I have no idea what Im doing here. This is my first repo. Im not a coder, Im an admin tht whittles sticks with a swiss army knife of bash, awk, sed, perl and php as needed for the most part. PHP is a nice easy gateway for people to adopt this script easily (since we want a big uptake to spread the load and keep it grassroots).

This thing definitely could use a refactor and cleanup as well, and any recommendations regarding improving this repo also appreciated.
