import re

class SambaLog:

    def __init__(self, logs = ["/var/log/samba/log.nmbd", "/var/log/samba/log.smbd"]):
        self.logs = logs
        self.rex = {
            "PDC" : "Samba server (\S+) is now a domain master browser for workgroup (\S+) on subnet (\S+)",
            "LOGON" : "Samba is now a logon server for workgroup (\S+) on subnet (\S+)",
            "STOP" : "going down...",
            "START" : "Netbios nameserver version (\S+) started.",
            "AUTHSUCCESS" : "authentication for user \[(\S+)\] -> \[(\S+)\] -> \[(\S+)\] succeeded",
            "AUTHFAILED" :  "Authentication for user \[(\S+)\] -> \[(\S+)\] FAILED with error (\S+)"
            }

    def get(self):
        return self.filterLog(self.parse())

    def filterLog(self, logs):
        filteredLogs = []
        for log in logs:
            for key in self.rex:
                m = re.search(self.rex[key], log["msg"])
                if m:
                    l = {}
                    l["day"] = log["day"]
                    l["hour"] = log["hour"]
                    l["msg"] = key
                    filteredLogs.append(l.copy())
        return filteredLogs

    def parse(self):
        logs = {}
        for logFile in self.logs:
            l = {}
            firstdate = 0
            f = file(logFile)
            for line in f:
                if line.startswith("["):
                    firstdate = 1
                    if l: logs.append(l.copy())
                    l = {}
                    l["msg"] = ""
                    m = re.match("\[(.*)\].*", line)
                    day, hour, num = m.group(1).split()
                    hour = hour[:-1]
                    l["day"] = day
                    l["hour"] = hour
                else:
                    if firstdate:
                        line = line.strip()
                        line = line.strip("*")
                        if len(line): l["msg"] = l["msg"] + line
            f.close()
        return logs
