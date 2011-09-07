#!/usr/bin/env python
# -*- coding: utf-8; -*-                                                                
# Example to apply network quotas from ldap to tc traffic shaping rules.
#
# (c) 2009 Open Systems Specilists - Glen Ogilvie                                       
#                                                                                       
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

""" Apply tc rules example """

# EXAMPLE ONLY:  You will need to modify this to suit your network.
# 
import ldap
import subprocess
import re
import warnings

# hide DepercationWarning when importing MySQLdb (comment out the 3 lines below if running python 2.5.x)
# with warnings.catch_warnings():
#     warnings.simplefilter("ignore", DeprecationWarning)
import MySQLdb

uri = "ldap://localhost:389"
base = "ou=People,dc=example,dc=com"
scope = ldap.SCOPE_SUBTREE
filterstr = "(objectClass=systemQuotas)"
attrlist = ['uid', 'networkquota', 'uidNumber']
filterstrall = "(objectClass=posixAccount)"
attrlistall = ['uid', 'uidNumber']
query = """select sum(bytes_in + bytes_out) as bytes, username from  
ulog where username is not null and bytes_in is not null and bytes_out is not null 
group by username;"""
# query = """SELECT SUM(bytes_in + bytes_out) AS bytes, username 
# FROM user_hourly_summary 
# WHERE username IS NOT NULL 
# AND bytes_in IS NOT NULL 
# AND bytes_out IS NOT NULL 
# AND TO_DAYS(`date`) > TO_DAYS(CURDATE() - INTERVAL 1 MONTH)
# GROUP BY username"""


# Test command to get tc filters.
tcfilterlist = "cat tcexample.txt"
# tcfilterlist = "tc filter show dev eth2"
tcapplycmd  = "./tcruleadd.sh %s %x"            # args: uid, uid in hex
tcremovecmd = "./tcruledel.sh %s %x"    



def getBytes(username, network):
    user = False
    for dn, record in res:
        if "networkquota" in record: 
#            print record["uid"][0]
            if record["uid"][0] == username:
                user = True
                for q in record["networkquota"]:
                    if q.split(',')[0] == network:
                        return q.split(',')[1]
                    
    if not user:
        raise NameError("username: %s not in ldap" % username)
    raise NameError("Getbytes function failed to find matching networkquota attribute value %s record for user: %s" % (network, username))
    
def getUid(username):
    for dn, record in resall:
            if record["uid"][0] == username:
                return record["uidNumber"][0]
    return False
                                                                                                                                              
def hasQuota(username):                                                                                                                    
    q = False                                                                                                                           
    for dn, record in res:                                                                                                                
        if "networkquota" in record:                                                                                                      
            if record["uid"][0] == username:                                                                                              
                q = True                                                                                                              
    return q
                   
def processNetworkQuotas(username, actualbytes):
    if not hasQuota(username):
        print "User: %s does not have a network quota set in ldap" % (username)
        removeRateLimiting(username)
        return False
    
    network = "Internet:0.0.0.0/0:any"
    quotabytes = getBytes(username, network)
    print "User: %s has used %s of %s bytes for network: %s" % (username, actualbytes, quotabytes , network)
    if int(actualbytes) > int(quotabytes):
        print "%s is over the limit by %d bytes" % (username, (int(actualbytes) - int(quotabytes)))
        applyRateLimiting(username)
    else:
        removeRateLimiting(username)
        
        
def applyRateLimiting(username):
    if (isRateLimited(username)):
        print "User: %s already rate limited" % (username)
        return True
    print "Rate limiting user: %s (%s)" % (username, getUid(username)) 
    uid = (getUid(username))
    print "CMD: " + tcapplycmd % (uid, int(uid))
    res = subprocess.call(tcapplycmd % (uid, int(uid)), shell=True)
    if res != 0:
        raise NameError("problem running apply cmd: %s" % tcapplycmd)
    return True        

def removeRateLimiting(username):
    if (isRateLimited(username)):
                print "removing quota for: " + username
                uid = (getUid(username))
                print "CMD: " + tcremovecmd % (uid, int(uid))
                res = subprocess.call(tcremovecmd % (uid, int(uid)), shell=True)
                if res != 0:
                    raise NameError("problem running remove cmd: %s" % tcremovecmd)
                return True
    return False 

def isRateLimited(username):
    print "Checking if user: %s (%s) is rate limited" % (username, getUid(username))
    # python regex
    p = re.compile('filter parent 1: protocol ip pref 1 fw handle 0x([0-9A-Fa-f]+) classid 1:[0-9A-Fa-f]+')
    for r in tcrules:
#        print "rule: " + r
        m = p.match(r)
        if m:
            uid = int(m.groups()[0], 16)
#            print "match found" + str(uid)
#            print "uid from username" + getUid(username)
            if uid == int(getUid(username)):
                print "matching rule found " + r
                return True
    return False
# connect to ldap
l = ldap.initialize(uri)
res = l.search_s(base, scope, filterstr, attrlist)
resall = l.search_s(base, scope, filterstrall, attrlistall)
print "\nLDAP Search results"
for dn, record in res:
    if "networkquota" in record:
#        print "Processing: " + repr(dn)
        print "%-15s: %s" % (record["uid"], record["networkquota"])
#    print repr(record)
l.unbind()


# do this only once, instead of every time function is called.   Read existing TC rules.
tcproc = subprocess.Popen(tcfilterlist, shell=True, stdout=subprocess.PIPE)
tcrules = tcproc.communicate()[0].split("\n")
if tcproc.returncode != 0:
    raise NameError("problem running cmd: %s" % tcfilterlist)

# connect to Mysql
db = MySQLdb.connect(passwd="passwd", db="nufw", host="laptop", user="root", port=13306)
c = db.cursor(MySQLdb.cursors.DictCursor)

#query = """select bytes_in + bytes_out as bytes, username from  
#ulog where username is not null and bytes_in is not null and bytes_out is not null;"""

c.execute(query)
print "\nMysql Results:"
results = c.fetchall()
# Sanity check.
if len(results) < 1:
    raise NameError("MySQL search did not find any users with traffic.  This is unexpected");

for x in results:
    print "%(username)-15s: %(bytes)s\n" % x
    processNetworkQuotas(x["username"], x["bytes"])
