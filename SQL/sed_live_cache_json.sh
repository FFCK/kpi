sed -i_old 's/@@END@@//' ./live/cache/*.json

find *.json -mtime +1200 -exec rm {} \;

rm *.json_old