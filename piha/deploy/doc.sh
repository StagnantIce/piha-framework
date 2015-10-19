#!/bin/bash
vendor/bin/phpdoc.php -t docs/ -d ../piha/ --template="responsive-twig"
firefox docs/index.html
