#!/bin/bash
# Görsel optimizasyonu için betik
find ./uploads -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" | xargs -I {} sh -c 'imagemin {} > {}.optimized && mv {}.optimized {}'