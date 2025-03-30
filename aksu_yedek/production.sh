#!/bin/bash
# CSS minify
find ./assets/css -name "*.css" -type f -not -name "*.min.css" | xargs -I {} sh -c 'cat {} | cleancss -o ${0%.css}.min.css' {}

# JavaScript minify
find ./assets/js -name "*.js" -type f -not -name "*.min.js" | xargs -I {} sh -c 'cat {} | uglifyjs -o ${0%.js}.min.js' {}

# index.php ve diğer dosyalarda .css ve .js uzantılarını .min.css ve .min.js olarak değiştir
find . -name "*.php" -type f | xargs sed -i 's/style\.css/style\.min\.css/g'
find . -name "*.php" -type f | xargs sed -i 's/script\.js/script\.min\.js/g'