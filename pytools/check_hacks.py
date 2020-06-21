import os, sys, json, MySQLdb, requests, random, string
from itertools import cycle, izip

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

def generate(length=16): return ''.join(random.choice(string.ascii_letters + string.digits) for i in range(length))

rKnownFiles = [ # Ignore /tmp/ui.php as it's not always accessible by XC, and it can't be used anyway.
    "%s/wwwdir/langs/Italian.php" % rBasePath,
    "%s/admin/assets/css/souza3.php" % rBasePath,
    "%s/php/include/php/main/fastcgi.php" % rBasePath,
    "%s/wwwdir/includes/geo/Database.php" % rBasePath
]

rNginxAllowed = [
    "%s/nginx/sbin/" % rBasePath,
    "%s/nginx/sbin/nginx" % rBasePath,
]

if __name__ == "__main__":
    print "THIS IS AN EXPERIMENTAL TOOL, ONLY CONTINUE IF YOU HAVE TO!"
    print "THIS CAN DO MORE DAMAGE THAN GOOD, WHO KNOWS?"
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
    for rServer in rServers:
        rInfected = False
        rFailed = False
        print "Checking Server #%d: %s" % (int(rServer[0]), rServer[1])
        print " "
        rAPI = "http://%s:%d/system_api.php" % (rServer[1], int(rServer[2]))
        # Check known files
        for rFile in rKnownFiles:
            rData = {"action": "getFile", "filename": rFile, "password": rPassword}
            try: rFileData = requests.post(rAPI, data=rData, timeout=5).content
            except:
                rFailed = True
                rFileData = ""
            if len(rFileData) > 0:
                rInfected = True
                print "Found infected file at: %s" % rFile
                rCommand = "rm -f \"%s\"" % rFile
                rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                try: rResponse = requests.post(rAPI, data=rData, timeout=5).content
                except: rFailed = True
        # Check NGINX
        rCommand = "/usr/bin/find \"" + rBasePath + "/nginx/sbin/\""
        rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
        try: rResponse = requests.post(rAPI, data=rData, timeout=5).content
        except:
            rFailed = True
            rResponse = None
        if rResponse:
            for rFile in json.loads(rResponse):
                if not rFile.lower() in rNginxAllowed:
                    rInfected = True
                    print "Found infected NGINX at: %s" % rFile
                    rCommand = "rm -f \"%s\"" % rFile
                    rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                    try: rResponse = requests.post(rAPI, data=rData, timeout=5).content
                    except: rFailed = True
        # Check Sudo
        rData = {"action": "runCMD", "command": "passwd --status xtreamcodes", "password": rPassword}
        try: rResponse = json.loads(requests.post(rAPI, data=rData, timeout=5).content)[0].split()
        except:
            rFailed = True
            rResponse = []
        if len(rResponse) > 1 and rResponse[0] == "xtreamcodes" and rResponse[1].upper() == "P":
            print " "
            rInfected = True
            print "Xtream Codes user has a password!"
            if int(rServer[0]) == int(rConfig["server_id"]):
                os.system("sudo passwd -d xtreamcodes >/dev/null 2>&1")
                os.system("sudo usermod -s /usr/sbin/nologin xtreamcodes >/dev/null 2>&1")
            else:
                rCommand = "sudo passwd -d xtreamcodes && sudo usermod -s /usr/sbin/nologin xtreamcodes"
                rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                rResponse = requests.post(rAPI, data=rData, timeout=5).content
            rData = {"action": "runCMD", "command": "passwd --status xtreamcodes", "password": rPassword}
            try: rResponse = json.loads(requests.post(rAPI, data=rData, timeout=5).content)[0].split()
            except: pass
            if len(rResponse) > 1 and rResponse[0] == "xtreamcodes" and rResponse[1].upper() == "P": print "Couldn't disable user password... Please disable it manually on the LB, check github notes."
            if int(rServer[0]) <> int(rConfig["server_id"]):  print "You may need to modify /etc/sudoers manually on this load balancer. Delete any duplicate after the first `xtreamcodes` line."
        # Main server only!
        if int(rServer[0]) == int(rConfig["server_id"]):
            rSudoers = open("/etc/sudoers", "r").read()
            if "xtreamcodes ALL=(ALL:ALL) NOPASSWD:ALL" in rSudoers:
                print "Xtream Codes user has unrestricted sudo! Fixing on main..."
                rSudoers = rSudoers.replace("xtreamcodes ALL=(ALL:ALL) NOPASSWD:ALL", "")
                rFile = open("/etc/sudoers", "w")
                rFile.write(rSudoers)
                rFile.close()
        # Check start_services.sh
        rFile = open(rBasePath + "/start_services.sh", "r").read()
        rNewServices = []
        rWriteServices = False
        for rLine in rFile.split("\n"):
            if rLine.startswith("echo"): rWriteServices = True
            else: rNewServices.append(rLine)
        if rWriteServices:
            print " "
            print "Xtream Codes start_services.sh is infected! Fixing..."
            rFile = open(rBasePath + "/start_services.sh", "w")
            rFile.write("\n".join(rNewServices))
            rFile.close()
            os.system("sudo chmod +x %s/start_services.sh" % rBasePath)
        # Report back
        if rFailed: print "Server failed to execute some commands, cannot confirm it's not infected! Please check the server status."
        elif not rInfected: print "Server not infected!"
        print " "
        print " "
    # Check MySQL remote access.
    for rServer in rServers:
        if int(rServer[0]) == int(rConfig["server_id"]):
            try: rRDB = MySQLdb.connect(host=rServer[1], user=rConfig["db_user"], passwd=rConfig["db_pass"], db=rConfig["db_name"], port=int(rConfig["db_port"]))
            except: rRDB = None
            if rRDB:
                print "Main MySQL server is accessible from outside world!"
                rQ = None
                while rQ not in ["Y", "N"]: rQ = raw_input("Would you like to secure your MySQL and only allow LB's to connect? (Enter Y/N) : ").upper()
                if rQ == "Y":
                    rRet = os.system("mysql -u root -e \"SELECT VERSION()\" >/dev/null 2>&1")
                    if rRet == 0:
                        print "Connected without password."
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
                    while rQ not in ["Y", "N"]: rQ = raw_input("Would you like to generate a new MySQL password? (Enter Y/N) : ").upper()
                    rNewPass = False
                    if rQ == "Y":
                        rNewPass = True
                        rMySQLPass = generate()
                        print " "
                        print "MySQL password changed to: %s" % rMySQLPass
                        print "SAVE THIS PASSWORD SOMEWHERE!"
                        print " "
                    else: rMySQLPass = rConfig["db_pass"]
                    rHosts = ["localhost", "127.0.0.1"] + rAddHosts
                    for rServer in rServers:
                        if int(rServer[0]) <> int(rConfig["server_id"]): rHosts.append(rServer[1])
                        if rNewPass:
                            print "Generating a new config on #%d: %s" % (int(rServer[0]), rServer[1])
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
                            rCommand = "echo \"%s\" > %s/config" % (rNewConfig, rBasePath)
                            rData = {"action": "runCMD", "command": rCommand, "password": rPassword}
                            rResponse = requests.post(rAPI, data=rData, timeout=5).content
                    rCommand = "REVOKE ALL PRIVILEGES ON *.* FROM '%s'@'%%';" % rConfig["db_user"]
                    if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                    else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                    rCommand = "DELETE FROM `mysql`.`user` WHERE `user` = '%s';" % rConfig["db_user"]
                    if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                    else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                    print " "
                    for rHost in rHosts:
                        print "Granted access from: %s" % rHost
                        rCommand = "GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, CREATE, TRUNCATE ON %s.* TO '%s'@'%s' IDENTIFIED BY '%s';" % (rConfig["db_name"], rConfig["db_user"], rHost, rMySQLPass)
                        if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                        else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                    rCommand = "FLUSH PRIVILEGES;"
                    if rRootPass: rRet = os.system("mysql -u root -p%s -e \"%s\" >/dev/null 2>&1" % (rRootPass, rCommand))
                    else: rRet = os.system("mysql -u root -e \"%s\" >/dev/null 2>&1" % rCommand)
                    rDB.commit()
            else: print "MySQL is secure from the outside world! :)"
            break
    print " "
    print " "
    print "Done! Run this again to be sure..."
