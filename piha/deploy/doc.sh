#!/bin/bash
vendor/bin/phpdoc -t docs/ -d /home/stagnantice/projects/vk/piha/ -i /home/stagnantice/projects/vk/piha/deploy/vendor/ --template="responsive-twig"
firefox docs/index.html
