from getData import getKline
import time
import string
import sys
import collections

file = "../../conf/db.conf.py"
x = getKline(file)


nowt = time.time()
lastt = time.time() - 365*24*60*60
now = time.strftime('%Y%m%d', time.localtime(nowt))
last = time.strftime('%Y%m%d', time.localtime(lastt))

b = x.getAllStock()
for i in b:
    i = "SH600745"
    str = i[:4]
    if str != "SH60" and str != "SZ00" and str != "SZ30":
        continue
    i = i.lower()
    print(i,last,now)
    kline = x.getAllLine(i,"day","0",last,now)
    num = 0
    sortL = collections.OrderedDict()
    allL = collections.OrderedDict()
    dataL = collections.OrderedDict()
    for k,v in kline.items():
        sortL[k] = v[1]
        newL = collections.OrderedDict()
        newL[0] = v
        newL[1] = num
        allL[k] = newL
        dataL[num] = v 
        num = num + 1
    sortL = sorted(sortL.items(), key=lambda d: d[1])
    final = collections.OrderedDict()
    tmp = sortL[0]
    minLow = tmp[1]
    minKey = tmp[0]
    snum = 0
    final[minKey] = allL[minKey]
    for m in sortL:
        low = m[1]
        lnum = allL[m[0]][1]
        if lnum > 1 and lnum < (num-1):
            before = dataL[lnum-1][1]
            after = dataL[lnum+1][1]
            if (before > low and after > low) or (num == lnum+1 and before > low):
                flag = 0
                for n in final:
                    if abs(lnum - final[minKey][1]) < 5:
                        flag = 1
                if flag == 0:
                    final[m[0]] = allL[m[0]]
    lastM = time.time() - 14*24*60*60
    lastMonth = time.strftime('%Y%m%d', time.localtime(lastM))
    newFinal = collections.OrderedDict()
    for r,s in final.items():
        low1 = s[0][1]
        if low1.count('.') == 1:
            left = int(low1.split('.')[0])%100
            right = low1.split('.')[1]
        else:
            left = low1
            right = "00"
        if left == right:
            if left<10 and ((low1-left) < 0.1):
                continue
            if r>lastMonth and r!=now and low1 <= minLow:
                newFinal[i] = final[r]
    print(newFinal)
    sys.exit(0)
