#!/bin/bash

echo "INFORMATION: This script aims to patch your symfony-1.4 distribution, because its development has stopped but is still needs to be maintained"
echo ""
echo "To continue press ENTER"
echo "To cancel press CTRL+C NOW !!"
read

echo ""
echo ""
echo "Patching framework..."
for elt in data/diff/*.diff
do
  patch -N -p0 < $elt
done
rm -f `find lib/vendor/ -iname '*.rej'`
rm -f `find lib/vendor/ -iname '*.orig'`

echo "done."
echo ""
