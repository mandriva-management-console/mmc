#!/usr/bin/python
# -*- coding: utf-8; -*-

from datetime import datetime, timedelta
import random
import MySQLdb as mysql

## report table
con = mysql.connect(
                    host='localhost',
                    db='report',
                    user='root',
                    passwd='linbox',
                   )

cur = con.cursor()
now = datetime.now()

cur.execute("DELETE FROM DiscSpace")
cur.execute("DELETE FROM RamUsage")

for d in xrange(0, 365):
    used = random.randrange(1, 100)
    free = 100 - used
    cur.execute("INSERT INTO DiscSpace(timestamp, used, free) VALUES ('%s', '%s', '%s')" % (now - timedelta(d), used, free))

    used = random.randrange(1, 100)
    free = 100 - used
    cur.execute("INSERT INTO RamUsage(timestamp, used, free) VALUES ('%s', '%s', '%s')" % (now - timedelta(d), used, free))
cur.close()

con.commit()
con.close()

# backuppc table
con = mysql.connect(
                    host='localhost',
                    db='backuppc',
                    user='root',
                    passwd='linbox',
                   )

cur = con.cursor()
now = datetime.now()

cur.execute("DELETE FROM report_server_used_disc_space")

for d in xrange(0, 365):
    used = random.randrange(1, 100)
    free = 100 - used
    cur.execute("INSERT INTO report_server_used_disc_space(timestamp, used, free, backup_server_id) VALUES ('%s', '%s', '%s', '%s')" % (now - timedelta(d), used, free, 1))

# Get backuppc hosts
cur.execute("SELECT id FROM hosts")
uuid_ids = cur.fetchall()

cur.execute("DELETE FROM report_used_disc_space_per_machine")

for d in xrange(0, 365):
    for uuid in uuid_ids:
        used = random.randrange(1, 100)
        cur.execute("INSERT INTO report_used_disc_space_per_machine(timestamp, used_disc_space, host_id) VALUES ('%s', '%s', '%s')" % (now - timedelta(d), used, uuid[0]))

cur.close()
con.commit()
con.close()
