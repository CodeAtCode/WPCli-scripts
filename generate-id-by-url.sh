#!/bin/bash

# Install:
#     wp package install codeAtcode/wp-cli-getbyurl
# How to use the script:
#     cat list.txt | xargs -n1 generate-id-by-url.sh

out=$(wp get-by-url "$1" --skip-plugins --skip-themes)
if [[ -n $out ]]; then
    command=$(cut -d'|' -f1 <<< "$out" | sed 's/^[[:blank:]]*//;s/[[:blank:]]*$//')
    id=$(cut -d'|' -f2 <<< "$out" | sed 's/^[[:blank:]]*//;s/[[:blank:]]*$//')
    taxonomy=$(cut -d'|' -f3 <<< "$out" | sed 's/^[[:blank:]]*//;s/[[:blank:]]*$//')
    if [[ "$taxonomy" == 'post' ]]; then
        echo $id >> ./ids.txt
    fi
fi
