#!/bin/bash
curl http://standards-oui.ieee.org/oui.txt -o oui.orig.txt
cat oui.orig.txt | grep "hex" | awk '{$2=""; print}' | tee oui.txt
php oui.php
rm oui.txt oui.orig.txt
