#!/usr/bin/python
# -*- coding: utf-8 -*-
import os, sys, json, MySQLdb, requests, random, string, socket
from itertools import cycle, izip

"""
 i forked this script from GTA's check_hacks.py script, if you want to change main server ip and db passwd on all lb servers, use this.

#dependencies 
sudo apt-get install python-mysqldb python-requests

#source is https://github.com/xtreamui/XCFILES



keep both new and old main servers running,
then run this script on old main.
"""



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
    print "This tool will update config files of every server with new main server ip."
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
                while rQ not in ["Y", "N"]: rQ = raw_input("Would you like to update main server ip and db passwd on all lb servers (Enter Y/N) : ").upper()
                rNewMainIP = False
                if rQ == "Y":
                    rNewMainIP = True
                else:
                    print "Exiting now"
                    sys.exit(1)
                
                try: 
                    new_main_ip = raw_input("New Main Server Ip: %s" % (" "*4))
                    new_db_pass = raw_input("New DB Password: %s" % (" "*7))
                    print " "
                except:
                    print "Invalid entries!"
                    sys.exit(1)

                rQuit = None
                while rQuit not in ["Y", "N"]: rQuit = raw_input("Do You Want to Continue? Last Warning!!! (Y/N) : ").upper()
                if rQuit == "N": sys.exit(1)
                # update config on each lb    
                for rServer in rServers:
                     #this adds lb ips to rHosts array
                    if int(rServer[0]) <> int(rConfig["server_id"]):
                        if rNewMainIP:
                            print "Generating a new config on #%d: %s" % (int(rServer[0]), rServer[1])
                            print " "
                            rAPI = "http://%s:%d/system_api.php" % (rServer[1], int(rServer[2]))
                            rData = {"action": "getFile", "filename": "%s/config" % rBasePath, "password": rPassword}
                            try: rOldConfig = decryptConfig(requests.post(rAPI, data=rData, timeout=5).content)
                            except:
                                # Couldn't get config? Make a new one.
                                if int(rServer[0]) == int(rConfig["server_id"]): rHost = "127.0.0.1"
                                else: rHost = rServer[1]
                                rOldConfig = {'server_id': str(rServer[0]), 'db_name': rConfig["db_name"], 'pconnect': '0', 'db_port': str(rConfig["db_port"]), 'db_pass': None, u'host': None, 'db_user': rConfig["db_user"]}
                            rOldConfig["host"] = '%s' % new_main_ip
                            rOldConfig["db_pass"] = '%s' % new_db_pass
                            rNewConfig = encryptConfig(rOldConfig)

                            # write LB's config with xc api
                            if int(rServer[0]) <> int(rConfig["server_id"]):
                                rCommand = "echo \"%s\" > %s/config" % (rNewConfig, rBasePath)
                                rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                                try: rResponse = requests.post(rAPI, data=rData, timeout=5).content
                                except requests.exceptions.ReadTimeout: 
                                    pass
                # send start_services.sh command with xc api
                for rServer in rServers:
                    if int(rServer[0]) <> int(rConfig["server_id"]):
                        print "Trying to restart services with new config on server #%d: %s" % (int(rServer[0]), rServer[1])
                        print " "
                        rAPI = "http://%s:%d/system_api.php" % (rServer[1], int(rServer[2]))
                        rCommand = "%s/start_services.sh" % rBasePath
                        rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                        try: rResponse = requests.post(rAPI, data=rData, timeout=2)
                        # don't wait response from api request, no need to wait it.
                        except requests.exceptions.ReadTimeout: 
                            pass
                rDB.commit()
            else:
                print "No MySQL connection!"
                sys.exit(1)
            break
    print " "
    print " "
    print "Done!"
