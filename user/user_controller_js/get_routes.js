import { drawPolyline, decodePolyline, clearPolylines, clearInfoWindows } from "./decode_and_draw_polyline.js";
import { showCustomRouteInfo, clearSelectPath } from "./show_custom_route_info.js";

let user = null;
export async function getRoutes( destination, map, userLocation ) {
    let processedRoutes = [];
    const apiKey = "API KEYS";
    const url = "https://routes.googleapis.com/directions/v2:computeRoutes";
    if (userLocation instanceof Promise) {
      userLocation = await userLocation;
  }

    if (!userLocation || typeof userLocation.latitude !== "number" || typeof userLocation.longitude !== "number") {
      console.error("❌ Invalid userLocation:", userLocation);
      return;
  }
    let formattedLocation = {
        lat: Number(userLocation.latitude), 
        lng: Number(userLocation.longitude)
    };
    if (!user) {
          user = new google.maps.Marker({
              map: map,
              position: formattedLocation,
              title: "ตำแหน่งของคุณ",
              animation: google.maps.Animation.DROP,
              draggable: true,
              icon: {
                  url: "https://cdn-icons-png.flaticon.com/128/3710/3710297.png",
                  scaledSize: new google.maps.Size(32, 32),
              },
          });
          google.maps.event.addListener(user, 'dragend', async function (event) {
            let newLocation = { latitude: event.latLng.lat(), longitude: event.latLng.lng() };
            clearPolylines();
            clearInfoWindows();
            clearSelectPath();
            await getRoutes(destination, map, newLocation);
        });
    } else {
        user.setPosition(formattedLocation);
    }
    let userorigin = { latitude: formattedLocation.lat, longitude: formattedLocation.lng };
    let userdestination = { latitude: destination.lat, longitude: destination.lng };
    const requestBody1 = {
      origin: {
        location: { latLng: userorigin },
      },
      destination: {
        location: { latLng: userdestination },
      },
      travelMode: "DRIVE",
      routingPreference: "TRAFFIC_UNAWARE",
      languageCode: "en-US",
      units: "IMPERIAL",
      computeAlternativeRoutes: true,
      requestedReferenceRoutes: "SHORTER_DISTANCE",
    };

    const requestBody2 = {
      origin: {
        location: { latLng: userorigin },
      },
      destination: {
        location: { latLng: userdestination },
      },
      travelMode: "WALK",
      languageCode: "en-US",
      units: "IMPERIAL",
      computeAlternativeRoutes: true,
    };

    const requestBody3 = {
      origin: {
        location: { latLng: userorigin },
      },
      destination: {
        location: { latLng: userdestination },
      },
      travelMode: "TWO_WHEELER",
      routingPreference: "TRAFFIC_UNAWARE",
      languageCode: "en-US",
      units: "IMPERIAL",
      computeAlternativeRoutes: true,
      requestedReferenceRoutes: "SHORTER_DISTANCE",
    };

    try {
      const response1 = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Goog-Api-Key": apiKey,
          "X-Goog-FieldMask":
            "routes.duration,routes.distanceMeters,routes.routeLabels,routes.polyline.encodedPolyline",
        },
        body: JSON.stringify(requestBody1),
      });

      const response2 = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Goog-Api-Key": apiKey,
          "X-Goog-FieldMask":
            "routes.duration,routes.distanceMeters,routes.routeLabels,routes.polyline.encodedPolyline",
        },
        body: JSON.stringify(requestBody2),
      });

      const response3 = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Goog-Api-Key": apiKey,
          "X-Goog-FieldMask":
            "routes.duration,routes.distanceMeters,routes.routeLabels,routes.polyline.encodedPolyline",
        },
        body: JSON.stringify(requestBody3),
      });

      const data1 = await response1.json();
      const data2 = await response2.json();
      const data3 = await response3.json();

      let isGetRouteBound = false;

      if (!isGetRouteBound) {
        document.getElementById("getRouteBtn").addEventListener("click", displayBestRoutes);
        isGetRouteBound = true;
      }

      async function displayBestRoutes() {
        clearPolylines();
        clearInfoWindows();
        clearSelectPath();
        processedRoutes = [];
        const allRoutes = [].concat(
          data1.routes || [],
          data2.routes || [],
          data3.routes || []
        );
        const sortedRoutes = allRoutes.sort(
          (a, b) => a.distanceMeters - b.distanceMeters
        );
        const bestRoutes = sortedRoutes.slice(0, 3);

        for (const route of sortedRoutes) {
          const { distanceMeters, duration, polyline } = route;
          const distanceText =
            distanceMeters >= 1000
              ? (distanceMeters / 1000).toFixed(2)
              : distanceMeters;
          const durationText = (parseInt(duration) / 60).toFixed(0);
          const routePoints = decodePolyline(polyline.encodedPolyline);

          let travelMode = (data1.routes && data1.routes.includes(route))
            ? "DRIVE"
            : (data2.routes && data2.routes.includes(route))
            ? "WALK"
            : "TWO_WHEELER";
          let idName = (data1.routes && data1.routes.includes(route))
            ? "Car"
            : (data2.routes && data2.routes.includes(route))
            ? "Walk"
            : "Motorcycle";
          let bestPathId = bestRoutes.includes(route) ? "BestPath" : null;
          const uniqueIdentifier = `${distanceText}-${durationText}`;
          if (!processedRoutes.includes(uniqueIdentifier)) {
            await showCustomRouteInfo(
              routePoints,
              durationText,
              distanceText,
              travelMode,
              idName
            );
            if (bestPathId) {
              await showCustomRouteInfo(
                routePoints,
                durationText,
                distanceText,
                travelMode,
                bestPathId
              );
            }
            await drawPolyline(
              routePoints,
              "#88f3fa",
              distanceText,
              idName,
              durationText,
              map,
            );
            processedRoutes.push(uniqueIdentifier);
          } else {
            console.log("ข้อมูลซ้ำ");
          }
        }
        document
          .querySelector("button[onclick=\"openCity(event, 'BestPath')\"]")
          .click();
      }
    } catch (error) {
      console.error("Error fetching route:", error);
    }
  }
