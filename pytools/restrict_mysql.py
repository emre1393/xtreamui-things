#!/usr/bin/python
# -*- coding: utf-8 -*-
import os, sys, json, MySQLdb, random, string
from itertools import cycle, izip


#dependencies 
# sudo apt-get install python-mysqldb


# ADDITIONAL HOSTS TO ALLOW IN MYSQL (OPTIONAL)
rAddHosts = []

# DO NOT MODIFY BELOW
rBasePath = "/home/xtreamcodes/iptv_xtream_codes"
rConfigPath = "%s/config" % rBasePath

def decryptConfig(rConfig):
    try: return json.loads(''.join(chr(ord(c)^ord(k)) for c,k in izip(rConfig.decode("base64"), cycle('5709650b0d7806074842c6de575025b1'))))
    except: return None

def encryptConfig(rConfig):
    return ''.join(chr(ord(c)^ord(k)) for c,k in izip(json.dumps(rConfig), cycle('5709650b0d7806074842c6de575025b1'))).encode('base64').replace('\n', '')

def generate(length=23): return ''.join(random.choice(string.ascii_letters + string.digits) for i in range(length))


if __name__ == "__main__":
    print " "
    print "This tool will drop current mysql user and add a mysql for each server in your servers table."
    print " "
    rQ = None
    while rQ not in ["Y", "N"]: rQ = raw_input("Continue? (Y/N) : ").upper()
    if rQ == "N": sys.exit(1)
    print " "
    # Decrypt Config
    rConfig = decryptConfig(open(rConfigPath, 'rb').read())
    rMySQLPass = rConfig["db_pass"]
    if rConfig["db_user"] == "root": 
        print "Your config file is using \"root\" user, this script won't work for your setup. Bye."
        sys.exit(1)
    # Connect to Database
    try:
        rDB = MySQLdb.connect(host=rConfig["host"], user=rConfig["db_user"], passwd=rConfig["db_pass"], db=rConfig["db_name"], port=int(rConfig["db_port"]))
        rCursor = rDB.cursor()
    except:
        print "No MySQL connection!"
        sys.exit(1)
    # Get LB Password
    rRet = rCursor.execute("SELECT `live_streaming_pass` FROM `settings`;")
    rPassword = rCursor.fetchall()[0][0]
    # Get Load Balancers
    rRet = rCursor.execute("SELECT `id`, `server_ip`, `http_broadcast_port` FROM `streaming_servers`;")
    rServers = rCursor.fetchall()

    # Check MySQL remote access.
    for rServer in rServers:
        if int(rServer[0]) == int(rConfig["server_id"]):
            # Try to Connect to Database
            try: rRDB = MySQLdb.connect(host=rConfig["host"], user=rConfig["db_user"], passwd=rConfig["db_pass"], db=rConfig["db_name"], port=int(rConfig["db_port"]))
            except: rRDB = None
            if rRDB:
                print " "
                # Try to Connect to db with root's  auth_socket
                rRet = os.system("mysql -u root -e \"SELECT VERSION()\" >/dev/null 2>&1")
                if rRet == 0:
                    print "Connected without password."
                    print " "
                    rRootPass = None
                else:
                    while True:
                        rRootPass = raw_input("Please enter your MySQL Root Password: ")
                        rRet = os.system("mysql -u root -p%s -e \"SELECT VERSION()\" >/dev/null 2>&1" % rRootPass)
                        if rRet == 0:
                            print "Connected!"
                            print " "
                            break
                        else: print "No MySQL connection! Try again..."

                print " "
                print "MySQL password didn't change, used same password from xc \"config\" file:"
                print " "
                print "=================================="
                print " "
                print "%s" % rConfig["db_pass"]
                print " "
                print "=================================="
                print " "
                # add your hosts into rHosts array    
                rHosts = ["localhost", "127.0.0.1"] + rAddHosts
                for rServer in rServers:
                     #this adds lb ips to rHosts array
                    if int(rServer[0]) <> int(rConfig["server_id"]): rHosts.append(rServer[1])

                # Delete existing db users  
                print " Dropping existing %s users" % rConfig["db_user"]
                rCommand = "DROP USER IF EXISTS '%s'@'%%'; DELETE FROM mysql.user WHERE user = '%s' AND host NOT IN ('%%');" % (rConfig["db_user"], rConfig["db_user"])
                if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)

                # add a mysql user for each host from rHosts array
                for rHost in rHosts:
                    print "Adding a new mysql user for : %s" % rHost
                    print " "
                    if rHost not in ["localhost", "127.0.0.1"]: 
                        rCommand = "DROP USER IF EXISTS '%s'@'%s'; GRANT SELECT, INSERT, UPDATE, DELETE ON %s.* TO '%s'@'%s' IDENTIFIED BY '%s';" % (rConfig["db_user"], rHost, rConfig["db_name"], rConfig["db_user"], rHost, rMySQLPass)
                    else: 
                        rCommand = "DROP USER IF EXISTS '%s'@'%s'; CREATE USER '%s'@'%s' IDENTIFIED BY '%s'; GRANT ALL PRIVILEGES ON %s.* TO '%s'@'%s' WITH GRANT OPTION; GRANT SELECT, PROCESS, LOCK TABLES ON *.* TO '%s'@'%s';" % (rConfig["db_user"], rHost, rConfig["db_user"], rHost, rMySQLPass, rConfig["db_name"], rConfig["db_user"], rHost, rConfig["db_user"], rHost)
                    if rRootPass: 
                        rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                    else: 
                        rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                rCommand = "FLUSH PRIVILEGES;"
                if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)

                os.system("/home/xtreamcodes/iptv_xtream_codes/start_services.sh")


                rDB.commit()
            else:
                print "No MySQL connection!"
                sys.exit(1)
            break
    print " "
    print " "
    print "Done!"
