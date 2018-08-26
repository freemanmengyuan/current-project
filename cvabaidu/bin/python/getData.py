import redis
import configparser
import json
import time
import collections


class getKline:

    r = None
    rn = None
    def __init__(self,file):
        rList = self.getRedis(file)
        self.r = rList[0]
        self.rn = rList[1]

    def getRedis(self,file):
        cf = configparser.ConfigParser()
        cf.read(file)

        host = cf.get("allRedis", "host")
        port = cf.getint("allRedis", "port")
        auth = cf.get("allRedis", "auth")
        index = cf.getint("allRedis", "index")

        pool = redis.ConnectionPool(host=host,port=port,db=index,password=auth,decode_responses=True)
        r = redis.Redis(connection_pool=pool)

        hostN = cf.get("nowRedis", "host")
        portN = cf.getint("nowRedis", "port")
        authN = cf.get("nowRedis", "auth")
        indexN = cf.getint("nowRedis", "index")

        poolN = redis.ConnectionPool(host=hostN,port=portN,db=indexN,password=authN,decode_responses=True)
        rn = redis.Redis(connection_pool=poolN)
        return [r,rn]

    def getAllStock(self):
        key = "stockAll"
        res = self.rn.get(key)
        arr = json.loads(res)
        return arr

    def getAllLine(self,stock,stype,fq,start,end):
        key = stock + "_" + stype + "_" + fq;
        startY = time.strftime("%Y",time.strptime(start, "%Y%m%d"))
        endY = time.strftime("%Y",time.strptime(end, "%Y%m%d"))
        result = collections.OrderedDict()
        if startY == endY:
            res = self.r.hget(key,startY)
            if res:
                arr = json.loads(res,object_pairs_hook=collections.OrderedDict)
                for i in arr:
                    if i >= start and i <= end:
                        result[i] = arr[i]
        elif startY < endY:
            year = startY
            while (year <= endY):
                res = self.r.hget(key,year)
                if res:
                    arr = json.loads(res,object_pairs_hook=collections.OrderedDict)
                    if year == startY or year == endY:
                        for i in arr:
                            if i >= start and i <= end:
                                result[i] = arr[i]
                    else:
                        for i in arr:
                            result[i] = arr[i]
                year = int(year) + 1
                year = str(year)
        return result

    def getMinLine(self,stock,stype,start,end):
        key = stock + "_min_" + stype;
        startY = time.strftime("%Y%m",time.strptime(start, "%Y%m%d%H%M"))
        endY = time.strftime("%Y%m",time.strptime(end, "%Y%m%d%H%M"))
        result = collections.OrderedDict()
        if startY == endY:
            res = self.r.hget(key,startY)
            if res:
                arr = json.loads(res,object_pairs_hook=collections.OrderedDict)
                for i in arr:
                    if i >= start and i <= end:
                        result[i] = arr[i]
        elif startY < endY:
            ym = startY
            while (ym <= endY):
                res = self.r.hget(key,ym)
                if res:
                    arr = json.loads(res,object_pairs_hook=collections.OrderedDict)
                    if ym == startY or ym == endY:
                        for i in arr:
                            if i >= start and i <= end:
                                result[i] = arr[i]
                    else:
                        for i in arr:
                            result[i] = arr[i]

                year = time.strftime("%Y",time.strptime(ym, "%Y%m"))
                month = time.strftime("%m",time.strptime(ym, "%Y%m"))
                month = int(month) + 1
                if (month>12):
                    month = 1
                    year = int(year) + 1
                if (month < 10):
                    ym = str(year) + "0" + str(month)
                else:
                    ym = str(year) + str(month)
        return result

#file = "../../conf/db.conf.py"
#x = getKline(file)
#a = x.getMinLine("sh600036","15","201706200930","201801301500")
#print(a)
