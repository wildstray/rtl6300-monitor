#!/usr/bin/env python3
import getopt
import json
import math
from pymongo import MongoClient
import pymongo
import requests
import sys
import time
import csv
import glob

def _import(csvfile, table):
    with open(csvfile, newline='') as csvfile:
        celltowers = csv.reader(csvfile, delimiter=';')
        i = 0
        for row in list(celltowers)[1:]:
            tech,mcc,mnc,lac_tac,node_id,cid,psc_pci,band,arfcn,site_name,cell_lat,cell_long,cell_name,azimuth,height,tilt_mech,tilt_el = row
            location = {'type': 'Point', 'coordinates': [float(cell_long), float(cell_lat)]}
            cell_name = cell_name.replace(site_name,'').rstrip()
            idx = {'mcc':int(mcc),'mnc':int(mnc),'enb':int(node_id)}
            try:
                band = int(band)
            except ValueError:
                band = 0
            cell = {'cid':int(cid),'band':band,'arfcn':int(arfcn),'description':cell_name}
            site = {'mcc':int(mcc),'mnc':int(mnc),'enb':int(node_id),'name':site_name,'location':location, 'cells': []}
            if not table.count_documents(idx):
                table.replace_one(idx, site, upsert=True)
                i += 1
            if not table.count_documents({'mcc':int(mcc),'mnc':int(mnc),'enb':int(node_id),'cells.cid':int(cid)}):
                table.update_one(idx, { '$push': { 'cells': cell } })
        print ("UPSERTED %d RECORDS" % i)

def main():
    if (len(sys.argv) < 2):
        print("Usage: %s <mongodb_connection_string>" % sys.argv[0])
        sys.exit(1)
    try:
        client = MongoClient(host=sys.argv[1], compressors="snappy,zlib")
        db = client.get_database()
        db.command('ping')
    except Exception as e:
        print(e)
        sys.exit(2)
    db = client.get_database()
    sites = db['sites']
    sites.create_index([('mcc',pymongo.DESCENDING), ('mnc',pymongo.DESCENDING), ('enb',pymongo.DESCENDING)], unique=True)
    sites.create_index([('mcc',pymongo.DESCENDING), ('mnc',pymongo.DESCENDING), ('enb',pymongo.DESCENDING), ('cells.cid',pymongo.DESCENDING)], unique=True)
    sites.create_index('name')
    sites.create_index([("location", "2dsphere")])
    for csvfile in glob.glob('*.csv'):
        print ("PROCESSING: %s" % csvfile)
        _import(csvfile, sites)

if __name__ == "__main__":
   main()
