#!/usr/bin/python3

import json
import os
import subprocess

if not os.path.exists('list.json'):
    print('Missing post list')

categories = {}
users = {}
meta = {}
meta_remove = ['_yoast_wpseo_title', '_yoast_wpseo_content_score', '_yoast_wpseo_primary_category']
i = 0

exports = open("/tmp/posts.csv","w")

with open('list.json') as f:
    posts = json.load(f)
    total = str(len(posts))
    print(total + ' posts')
    for post in posts:
        i += 1
        tax = ''
        print(' ' + str(i) + '/' + total + ' Post importing')

        for term in post['categories']:
            if not term['slug'] in categories:
                categories[term['slug']] = (subprocess.run(
                                'wp term create category "%s" --slug="%s" --porcelain' % (term['name'], term['slug'])
                            , shell=True, stdout=subprocess.PIPE, encoding="utf-8").stdout).rstrip("\r\n")

            print('Creating taxonomy ' + term['slug'] + ' at ' + categories[term['slug']])

            tax += categories[term['slug']] + ','
        tax = tax[:-1]

        for item in post['meta']:
            if not any(x in item['meta_key'] for x in meta_remove):
                meta[item['meta_key']] = item['meta_value']

        print('Importing posts')
        f = open("/tmp/post.txt","w")
        f.write(str(post['post_content']))
        f.close()
        post_id = (subprocess.run(
                        'wp post create /tmp/post.txt --post_title="' + str(post['post_title']).replace('"', "") +
                        '" --post_status=' + str(post['post_status']) +
                        ' --post_name="' + str(post['post_name']) +
                        '" --post_date="' + str(post['post_date']) +
                        '" --post_type=post --post_category=' + tax +
                        ' --meta_input=' + "'" + json.dumps(meta).replace("'", "") + "'" +
                        ' --porcelain'
                    , shell=True, stdout=subprocess.PIPE, encoding="utf-8").stdout).rstrip("\r\n")

        url = (subprocess.run(
                        'wp post get ' + str(post_id) + ' --field=post_name'
                    , shell=True, stdout=subprocess.PIPE, encoding="utf-8").stdout).rstrip("\r\n")
        exports.write(str(post['post_name']) + ',' + url + "\n")

        if '_thumbnail_url' in post:
            print('Import attachment ' + post['_thumbnail_url'])
            try:
                subprocess.check_call(
                        'wp media import "' + str(post['_thumbnail_url']) +
                        '" --post_id="' + post_id + '"' +
                        ' --featured_image'
                    , shell=True, stdout=subprocess.PIPE, encoding="utf-8")
            except subprocess.CalledProcessError:
              subprocess.check_call(
                        'wp media import "' + str(post['_thumbnail_url']) +
                        '" --post_id="' + post_id + '"' +
                        ' --featured_image'
                    , shell=True, stdout=subprocess.PIPE, encoding="utf-8")

exports.close()
