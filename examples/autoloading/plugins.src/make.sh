#!/bin/bash

for DIR in *; do
	test -d $DIR && pushd $DIR && 7z a -tzip ${DIR}.zip . && mv *.zip .. && popd
done
mv -fv *.zip ../plugins/
