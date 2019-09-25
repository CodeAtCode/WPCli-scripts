#!/usr/bin/python

# Generate the list of tags
# wp comment list --fields=ID,comment_date --format=json > comments.json

import json
import os
import datetime
import sys

i = 0
with open('comments.json') as f:
    comments = json.load(f)
    print('Found ' + str(len(comments)) + ' comments')
    print('Processing...')
    for comment in comments:
        date = datetime.datetime.strptime(comment['comment_date'], '%Y-%m-%d %H:%M:%S')
        if date.year == int(sys.argv[1]):
            i += 1
            os.system("wp comment delete " + comment['comment_ID'])

print('Removed ' + str(i) + ' comments')
