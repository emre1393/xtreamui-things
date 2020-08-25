#!/usr/bin/python
# -*- coding: utf-8 -*-
import os, sys, json, MySQLdb, requests, random, string
from itertools import cycle, izip

# i forked this script from GTA's check_hacks.py script, it re-generates db users and config files with new db password.

#dependencies 
# sudo apt-get install python-mysqldb python-requests

#source is https://github.com/xtreamui/XCFILES

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
    print " "
    print "I have forked this from check_hacks.py"
    print " "
    print "This tool will regenerate mysql password and update config files of every server"
    print " "
    print "ENSURE ALL OF YOUR LOAD BALANCERS ARE RUNNING AND WORKING FIRST"
    print " "
    rQ = None
    while rQ not in ["Y", "N"]: rQ = raw_input("Continue? (Y/N) : ").upper()
    if rQ == "N": sys.exit(1)
    print " "
    # Decrypt Config
    rConfig = decryptConfig(open(rConfigPath, 'rb').read())
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
                rQ = None
                # Ask last time, N=exit
                while rQ not in ["Y", "N"]: rQ = raw_input("Would you like to generate a new MySQL password? (Enter Y/N) : ").upper()
                rNewPass = False
                if rQ == "Y":
                    rNewPass = True
                    rMySQLPass = generate()
                    print " "
                    print "MySQL password changed to:"
                    print " "
                    print "=================================="
                    print " "
                    print "%s" % rMySQLPass
                    print " "
                    print "=================================="
                    print " "
                    print "SAVE THIS PASSWORD SOMEWHERE!"
                    print " "
                    print " "
                else:
                    print "Exiting now"
                    sys.exit(1)
                # add your hosts into rHosts array    
                rHosts = ["localhost", "127.0.0.1"] + rAddHosts
                for rServer in rServers:
                     #this adds lb ips to rHosts array
                    if int(rServer[0]) <> int(rConfig["server_id"]): rHosts.append(rServer[1])
                    if rNewPass:
                        print "Generating a new config on #%d: %s" % (int(rServer[0]), rServer[1])
                        print " "
                        rAPI = "http://%s:%d/system_api.php" % (rServer[1], int(rServer[2]))
                        rData = {"action": "getFile", "filename": "%s/config" % rBasePath, "password": rPassword}
                        try: rOldConfig = decryptConfig(requests.post(rAPI, data=rData, timeout=5).content)
                        except:
                            # Couldn't get config? Make a new one.
                            if int(rServer[0]) == int(rConfig["server_id"]): rHost = "127.0.0.1"
                            else: rHost = rServer[1]
                            rOldConfig = {'server_id': str(rServer[0]), 'db_name': rConfig["db_name"], 'pconnect': '0', 'db_port': str(rConfig["db_port"]), 'db_pass': None, u'host': rHost, 'db_user': rConfig["db_user"]}
                        rOldConfig["db_pass"] = rMySQLPass
                        rNewConfig = encryptConfig(rOldConfig)

                        # write main's config without api
                        if int(rServer[0]) == int(rConfig["server_id"]):
                            os.system("echo \"%s\" > %s/config" % (rNewConfig, rBasePath))

                        # write LB's config with xc api  
                        else: 
                            rCommand = "echo \"%s\" > %s/config" % (rNewConfig, rBasePath)
                            rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                            rResponse = requests.post(rAPI, data=rData, timeout=5).content

                # Delete existing db users except the one with % host            
                rCommand = "DELETE FROM mysql.user WHERE user = '%s' AND host NOT IN ('%%');" % rConfig["db_user"]
                if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)

                # add a mysql user for each host from rHosts array
                for rHost in rHosts:
                    print "Granted access from: %s" % rHost
                    print " "
                    if rHost <> ["localhost", "127.0.0.1"]: rCommand = "GRANT SELECT, INSERT, UPDATE, DELETE ON %s.* TO '%s'@'%s' IDENTIFIED BY '%s';" % (rConfig["db_name"], rConfig["db_user"], rHost, rMySQLPass)
                    else: rCommand = "CREATE USER '%s'@'%s' IDENTIFIED BY '%s'; GRANT ALL PRIVILEGES ON %s.* TO '%s'@'%s' WITH GRANT OPTION; GRANT SELECT, LOCK TABLES ON *.* TO '%s'@'%s';" % (rConfig["db_user"], rHost, rMySQLPass, rConfig["db_name"], rConfig["db_user"], rHost, rConfig["db_user"], rHost)
                    if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                    else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                rCommand = "FLUSH PRIVILEGES;"
                if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)

                # send start_services.sh command with xc api
                for rServer in rServers:
                    print "Trying to restart services with new config on server #%d: %s" % (int(rServer[0]), rServer[1])
                    print " "
                    rAPI = "http://%s:%d/system_api.php" % (rServer[1], int(rServer[2]))
                    rCommand = "%s/start_services.sh" % rBasePath
                    rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                    try: rResponse = requests.post(rAPI, data=rData, timeout=2)
                    # don't wait response from api request, no need to wait it.
                    except requests.exceptions.ReadTimeout: 
                        pass

                # drop mysql user with % host from db
                rCommand = "DROP USER IF EXISTS '%s'@'%%';" % rConfig["db_user"]
                if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                rDB.commit()
            else:
                print "No MySQL connection!"
                sys.exit(1)
            break
    print " "
    print " "
    print "Done!"
