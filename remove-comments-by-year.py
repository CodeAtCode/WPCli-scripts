#!/usr/bin/python

# Generate the list of comments
# wp comment list --fields=ID,comment_date --format=json > comments.json

# This script require a parameter that is the year

import json
import os
import datetime
import sys

i = 0
ids = ''
with open('comments.json') as f:
    comments = json.load(f)
    print('Found ' + str(len(comments)) + ' comments')
    print('Processing...')
    for comment in comments:
        date = datetime.datetime.strptime(comment['comment_date'], '%Y-%m-%d %H:%M:%S')
        if date.year == int(sys.argv[1]):
            i += 1
            ids += comment['comment_ID'] + ' '

os.system("wp comment delete " + ids + ' --force')

print('Removed ' + str(i) + ' comments')
