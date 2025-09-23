export function decodePolyline(encoded) {
    let polyline = [];
    let index = 0,
      lat = 0,
      lng = 0;
    while (index < encoded.length) {
      let shift = 0,
        result = 0,
        b;
      do {
        b = encoded.charCodeAt(index++) - 63;
        result |= (b & 0x1f) << shift;
        shift += 5;
      } while (b >= 0x20);
      lat += result & 1 ? ~(result >> 1) : result >> 1;

      shift = 0;
      result = 0;
      do {
        b = encoded.charCodeAt(index++) - 63;
        result |= (b & 0x1f) << shift;
        shift += 5;
      } while (b >= 0x20);
      lng += result & 1 ? ~(result >> 1) : result >> 1;

      polyline.push({ lat: lat / 1e5, lng: lng / 1e5 });
    }
    return polyline;
  }

  let selectedPolyline = null,
  listpolyline = [],
  infoWindows = {};

export async function drawPolyline(
    routePoints,
    color,
    distanceText,
    idName,
    duration,
    map
  ) {
    const borderPolyline = new google.maps.Polyline({
      path: routePoints,
      strokeColor: "blue",
      strokeOpacity: 1,
      strokeWeight: 7,
      zIndex: 1,
    });
    const polyline = new google.maps.Polyline({
      path: routePoints,
      geodesic: true,
      strokeColor: color,
      strokeOpacity: 1,
      strokeWeight: 5,
      zIndex: 2,
    });

    google.maps.event.addListener(polyline, "click", function () {
      selectRoute(polyline);
    });

    listpolyline.push({
      routePoints: JSON.stringify(routePoints),
      polyline,
      borderPolyline,
      idName,
      distanceText,
      duration,
    });

    function parseDistance(text) {
      if (typeof text === "string") {
        const kmMatch = text.match(/([\d.]+)\s*กม/);
        const mMatch = text.match(/([\d.]+)\s*ม/);
        if (kmMatch) return parseFloat(kmMatch[1]) * 1000;
        if (mMatch) return parseFloat(mMatch[1]);
      }
      return typeof text === "number" ? text : Infinity;
    }
    listpolyline.sort((a, b) => parseDistance(a.distanceText) - parseDistance(b.distanceText));
    const top3Routes = listpolyline.slice(0, 3);    

    function handleRouteSelection(routeType) {
      const filteredRoutes = listpolyline.filter(
        (item) => item.idName === routeType
      );
      if (filteredRoutes.length === 0) {
        clearInfoWindows();
      }
      filteredRoutes.sort((a, b) => a.distanceText - b.distanceText);
      filteredRoutes.forEach((item) => {
        item.borderPolyline.setMap(map);
        item.polyline.setMap(map);
        showWindowInfo(
          item.idName,
          JSON.parse(item.routePoints),
          item.duration,
          item.distanceText,
          false
        );
      });

      listpolyline.forEach((item) => {
        if (item.idName !== routeType) {
          item.borderPolyline.setMap(null);
          item.polyline.setMap(null);
        }
      });

      if (filteredRoutes.length > 0) {
        selectRoute(filteredRoutes[0].polyline);
      }
    }

    document
      .getElementById("top3ShortestRoutes")
      .addEventListener("click", function () {
        listpolyline.forEach((item) => {
          if (top3Routes.includes(item)) {
            item.borderPolyline.setMap(map);
            item.polyline.setMap(map);
            showWindowInfo(
              item.idName,
              JSON.parse(item.routePoints),
              item.duration,
              item.distanceText,
              true
            );
          } else {
            item.borderPolyline.setMap(null);
            item.polyline.setMap(null);
          }
        });

        if (listpolyline.length > 0) {
          selectRoute(top3Routes[0].polyline);
        }
      });

    document.getElementById("btnCar").addEventListener("click", function () {
      handleRouteSelection("Car");
    });

    document
      .getElementById("btnMotorcycle")
      .addEventListener("click", function () {
        handleRouteSelection("Motorcycle");
      });

    document.getElementById("btnWalk").addEventListener("click", function () {
      handleRouteSelection("Walk");
    });

    document
      .getElementById("BestPath")
      .addEventListener("click", handleRouteClick);
    document.getElementById(idName).addEventListener("click", handleRouteClick);
    document.getElementById("map").addEventListener("click", handleRouteClick);

    function handleRouteClick(event) {
      const closestPath = event.target.closest("#selectPath") || event.target.closest("#infoWindow");

      if (closestPath) {
        const clickedRoutePoints =
          closestPath.getAttribute("data-route-points");
        const found = listpolyline.find(
          (item) =>
            JSON.stringify(item.routePoints) ===
            JSON.stringify(clickedRoutePoints)
        );

        if (found) {
          selectRoute(found.polyline);
        } else {
          console.log("ไม่พบ polyline ที่ตรงกับ routePoints");
        }
      } else {
        console.log("ไม่พบ .selectBestPath ใน target");
      }
    }

    function showWindowInfo(
      idName,
      routePoints,
      duration,
      distanceText,
      topthree
    ) {
      const middleIndex = Math.floor(routePoints.length / 2);
      const centroidLat = routePoints[middleIndex].lat;
      const centroidLng = routePoints[middleIndex].lng;

      const travelIcon = {
        Car: "fa fa-car",
        Motorcycle: "fa fa-motorcycle",
        Walk: "fa fa-walking",
      }[idName];
      
      let distance = parseFloat(distanceText);
      if (typeof distance === "number" && !isNaN(distance)) {
        distance =
          distance.toString() === distance.toFixed(1) || distance.toString() === distance.toFixed(2)
            ? distance + " กม."
            : distance + " ม.";
      }
      
      const contentWindow = `
              <div id="infoWindow" data-route-points=${JSON.stringify(
                routePoints
              )}>
              <i class="${travelIcon}" style="font-size:24px; color:black"></i>
                <div>
                  <span id="distance">${distance}</span> <br>
                  <span id="duration">${duration} นาที</span>
                </div>
              </div>
          `;

      const posit = new google.maps.LatLng(centroidLat, centroidLng);
      const uniqueId = `${idName}-${distance}`;

      for (const key in infoWindows) {
        if (!key.startsWith(idName) && topthree === false) {
          infoWindows[key].close();
        }
        const shouldKeep = top3Routes.some((route) =>
          key.startsWith(route.idName)
        );
        if (!shouldKeep) {
          infoWindows[key].close();
        }
      }

      if (infoWindows[uniqueId]) {
        infoWindows[uniqueId].setPosition(posit);
        infoWindows[uniqueId].setContent(contentWindow);
      } else {
        infoWindows[uniqueId] = new google.maps.InfoWindow({
          content: contentWindow,
          position: posit,
        });
      }

      infoWindows[uniqueId].open(map);
    }

    function selectRoute(polyline) {
      if (selectedPolyline) {
        selectedPolyline.setOptions({
          strokeColor: selectedPolyline.originalColor,
          zIndex: 1,
        });
      }
      polyline.setOptions({ strokeColor: "blue", zIndex: 999 });
      selectedPolyline = polyline;
      polyline.originalColor = color;
      map.panTo(routePoints[0]);
    }
  }

  export function clearPolylines() {
    listpolyline.forEach(item => {
      item.polyline.setMap(null);
      item.borderPolyline.setMap(null);
    });
    listpolyline = [];
  }

  export function clearInfoWindows() {
    for (const key in infoWindows) {
      infoWindows[key].close();
    }
    infoWindows = {};
  }
    