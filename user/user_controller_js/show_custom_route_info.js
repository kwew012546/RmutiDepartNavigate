export async function showCustomRouteInfo(
    routePoints,
    duration,
    distanceText,
    travel,
    idName
  ) {
    const pathContainer = document.getElementById(idName);
    let distance = parseFloat(distanceText);
    const travelIcons = {
      DRIVE: "fa fa-car",
      TWO_WHEELER: "fa fa-motorcycle",
      WALK: "fa fa-walking",
    };
    const travelTexts = {
      DRIVE: "เดินทางด้วยรถยนต์",
      TWO_WHEELER: "เดินทางด้วยรถจักรยานยนต์",
      WALK: "เดินทางด้วยทางเท้า",
    };

    const travelIcon = travelIcons[travel];
    const travelText = travelTexts[travel];

    if (typeof distance === "number" && !isNaN(distance)) {
      distance =
        distance.toString() === distance.toFixed(1) || distance.toString() === distance.toFixed(2)
          ? distance + " กิโลเมตร"
          : distance + " เมตร";
    }

    const contentString = `<div id="selectPath" data-route-points=${JSON.stringify(
      routePoints
    )}>
                <i class="${travelIcon}" style="font-size:24px; color:black"></i>
                <strong>${travelText}</strong>
                <div>
                    <strong>ระยะทาง:</strong> <span>${distance}</span> <br>
                    <strong>ระยะเวลา:</strong> <span>${duration} นาที</span>
                </div>
            </div>`;
    pathContainer.innerHTML += contentString;
  }

export function clearSelectPath() {
  const selectPaths = document.querySelectorAll("#selectPath");
  selectPaths.forEach((path) => {
      path.remove();
  });
}
