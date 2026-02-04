# Askey RTL6300-D354 monitor

## History behind...
During 2025 I subscribed a 5G unlimited SIM with Alpsim, an Italian MNVO. In a first time, I decided to build a 5G router myself. It worked, but it was huge, ugly, it would have ruined the aesthetics of my home (due to thick walls, I had to install the router outside).
So I begun looking for an used, tiny, cheap 5G router. I found many Askey RTL6300-D354 routers, commonly used by Austrian A1 Telekom Group for FWA connections (bundled with a Frizbox router). These outdoor routers are solid and weatherproof, simple passthrough router (with a basic configuration, ip of 5G connection is assigned to the inside downstream router).

I bought one of these routers and installed outside my home. It's reliable, but it hasn't monitoring options such as a full feature webUI, it hasn't snmp, it could show only if connected or not and some basic network informations. No LTS/5G informations at all.

## Some hacking...

Doing some reverse engineering on the firmware and looking for information on this router on GitHub, I found some interesting RESTful endpoint to obtain hardware, network and mobile network informations:

https://github.com/cretudorin/askey_rtl6300_5g_cpe

So I created a simple HTML+js (using jQuery) webUI that displays tables containing the informations gathered:

<img alt="rtl6300-monitor screenshot 1" src="https://github.com/wildstray/rtl6300-monitor/blob/main/screenshot1.png" width="250" />

<img alt="rtl6300-monitor screenshot 2" src="https://github.com/wildstray/rtl6300-monitor/blob/main/screenshot2.png" width="250" />

I was happy with this, but I discovered that the router sometimes was connecting to a cell tower, sometimes to another cell tower... I were using this website to look for the cell towers, using these parameters: mcc, mnc and enb.

https://lteitaly.it/

I needed to see the elevation profile from my home to the cell towers. So I used Goole Earth client, positioning markers, creating a route and showing the elevation profile... it was difficult, overcomplicated, taking a long time! :-(

## And mapping...

LTE Italy (once a time........ maybe for members?) provided the possibility to download CSV of operators cell towers. So I wrote a python scritp to create a mongodb structure and import these cell towers informations.
Then I wrote some really basic RESTful endpoint to easily configure and obtain CPE location, BTS location by mcc, mnc and enb, BTS list by location. Then I added a map to my webUI with a marker of my home position and a marker of cell tower in use, querying the RESTful endpoints.
Then I added the elevation profile, querying ARCGis elevation APIs.

<img alt="rtl6300-monitor screenshot 3" src="https://github.com/wildstray/rtl6300-monitor/blob/main/screenshot3.png" width="250" />

## Library used

I used these free and open source projects:

https://jquery.com/

https://leafletjs.com/

https://github.com/ilyankou/Leaflet.IconMaterial

https://github.com/Raruto/leaflet-elevation

https://github.com/leaflet-extras/leaflet-providers

