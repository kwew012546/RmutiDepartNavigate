let marker;

async function initMap() {
  const position = { lat: 14.98747028934542, lng: 102.11796446410003 };

  var myStyles = [
    {
        featureType: "poi",
        elementType: "labels",
        stylers: [
            { visibility: "off" }
        ]
    }
];

var myOptions = {
    zoom: 17,
    center: position,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    styles: myStyles,
    clickableIcons: false
};

var map = new google.maps.Map(document.getElementById('map'), myOptions);

 marker = new google.maps.Marker({
    map: map,
    position: position,
    animation: google.maps.Animation.DROP,
    icon: {
      url: "../images/rmuti.png",
      scaledSize: new google.maps.Size(20, 40),
      anchor: new google.maps.Point(10, 30),
    },
  });

  var buildingPosition = new google.maps.Marker({
      map: map,
      position: position,
      title: "ตำแหน่งที่ต้องการ",
      animation: google.maps.Animation.DROP,
      draggable: true,
      icon: {
          url: "https://cdn-icons-png.flaticon.com/128/9131/9131546.png",
          scaledSize: new google.maps.Size(32, 32),
      },
  });
  
  const defaultIcon = {
    url: "https://cdn-icons-png.flaticon.com/128/7945/7945007.png",
    scaledSize: new google.maps.Size(32, 32),
  };

  class CustomLabelOverlay extends google.maps.OverlayView {
    constructor(position, text, map, header) {
      super();
      this.position = position;
      this.text = text;
      this.div = null;
      this.header = header;
      this.setMap(map);
    }
  
    onAdd() {
      this.div = document.createElement("div");
      if (this.header) {
        this.div.style.cssText = `
        position: absolute;
        width: 30%;
        font-size: 16px;
        color: #FF7100;
        margin-left: -90px;
        margin-top: 40px;
        font-weight: bold;
        text-align: center;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        textAlign: center;
      `;
      } else {
        this.div.style.cssText = `
        position: absolute;
        width: 20%;
        margin-left: 65px;
        font-size: 14px;
        color: black;
      `;
      }
      this.div.innerText = this.text;
  
      const panes = this.getPanes();
      panes.overlayLayer.appendChild(this.div);
    }
  
    draw() {
      const projection = this.getProjection();
      const position = projection.fromLatLngToDivPixel(this.position);
      if (this.div) {
        this.div.style.left = `${position.x - 50}px`;
        this.div.style.top = `${position.y - 35}px`;
      }
    }
  
    onRemove() {
      if (this.div) {
        this.div.parentNode.removeChild(this.div);
        this.div = null;
      }
    }
  }

  const response = await fetch("../user/user_controller_php/department_position.php");
  const data = await response.json();
  const locations = data.locations;
  const createdPositions = new Set();
  
  locations.forEach((loc) => {
    const lat = parseFloat(loc.lat);
    const lng = parseFloat(loc.lng);
    const key = `${lat},${lng}`;
  
    if (createdPositions.has(key)) {
      return;
    }
  
    createdPositions.add(key);
    const markerDestination = new google.maps.Marker({
      position: { lat, lng },
      map,
      title: loc.building_name,
      animation: google.maps.Animation.DROP,
      icon: defaultIcon,
    });
  
    new CustomLabelOverlay(
      new google.maps.LatLng(lat, lng),
      loc.building_name,
      map,
      false
    );
  });  
  
  google.maps.event.addListener(buildingPosition, 'dragend', async function (event) {
    const newLocation = { latitude: event.latLng.lat(), longitude: event.latLng.lng() };
    document.querySelector('#lat').value = newLocation.latitude;
    document.querySelector('#lng').value = newLocation.longitude;
  });
  new CustomLabelOverlay(position, "มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน (สุรนารายณ์)", map, true);
}
initMap();

window.toggleTimeFields = function() {
  let selectedWorkday = document.querySelector('input[name="workday"]:checked').value;
  let extraTimeFields = document.getElementById("extraTimeFields");

  extraTimeFields.innerHTML = '<br>';

  if (selectedWorkday === "Monday-Saturday" || selectedWorkday === "Everyday") {
      let labelStart = document.createElement("label");
      labelStart.innerText = "เวลาทำการ (วันเสาร์หรืออาทิตย์): ";

      let inputStart = document.createElement("input");
      inputStart.type = "time";
      inputStart.name = "weekend_timestart";
      inputStart.value = "08:30";
      
      let labelStop = document.createElement("label");
      labelStop.innerText = " ถึง ";

      let inputStop = document.createElement("input");
      inputStop.type = "time";
      inputStop.name = "weekend_timestop";
      inputStop.value = "16:30";

      extraTimeFields.appendChild(labelStart);
      extraTimeFields.appendChild(inputStart);
      extraTimeFields.appendChild(labelStop);
      extraTimeFields.appendChild(inputStop);
  }
}

let serviceCount = 1;

window.addService = function () {
  serviceCount++;
  var container = document.getElementById('services_container');

  var newService = document.createElement('div');
  newService.classList.add('service_entry');
  newService.setAttribute('data-service-id', serviceCount);

  newService.innerHTML = `
      <label>ข้อมูลการบริการ ${serviceCount}:</label>
      <input type="text" name="service[]" id="service" data-index="${serviceCount}"><br><br>

      <label>คำอธิบาย:</label>
      <input type="text" name="service_description[]" id="service_description" data-index="${serviceCount}"><br><br>

      <label>คำสำคัญ:</label>
      <input type="text" name="keyword[]" id="keyword" data-index="${serviceCount}" required>
      <span class="error-message">**</span><br><br>

      <label>อยู่ชั้นที่:</label>
      <input type="number" name="floor[]" id="floor" data-index="${serviceCount}" required>
      <span class="error-message">**</span><br><br>
      
      <label>หมายเลขห้อง:</label>
      <input type="text" name="room[]" id="room" data-index="${serviceCount}" placeholder='ตัวอย่าง "18-315"' required>
      <span class="error-message">**</span><br><br>

      <button type="button" onclick="removeService(this)">ลบข้อมูลบริการ</button>
      <hr>
  `;
  container.appendChild(newService);
}

window.removeService = function (button) {
  const serviceDiv = button.closest('.service_entry');
  serviceDiv.remove();
}

document.getElementById("clear").addEventListener("click", function(e) {
  e.preventDefault();
  const form = document.getElementById("agencyForm");
  form.reset();
  document.getElementById("extraTimeFields").innerHTML = '';
  const servicesContainer = document.getElementById("services_container");
  servicesContainer.innerHTML = `
      <div class="service_entry">
          <label>ข้อมูลการบริการ 1:</label>
          <input type="text" name="service[]" id="service" data-index="1"><br><br>
          <label>คำอธิบาย:</label>
          <input type="text" name="service_description[]" id="service_description" data-index="1"><br><br>
          <label>คำสำคัญ:</label>
          <input type="text" name="keyword[]" id="keyword" data-index="1" required>
          <span class="error-message">**</span><br><br>
          <label>อยู่ชั้นที่:</label>
          <input type="number" name="floor[]" id="floor" data-index="1" required>
          <span class="error-message">**</span><br><br>
      </div>
  `;
  serviceCount = 1;
});

$(document).ready(function() {
  $('#agencyForm').on('submit', function(e) {
      e.preventDefault();
      var formData = new FormData(this);
      $.ajax({
          url: 'admin_controller/insert_data.php',
          method: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            if (response.trim() === "success") {
                alert("เพิ่มข้อมูลสำเร็จ");
                $('#agencyForm')[0].reset();
                window.location.reload();
            } else {
                console.log("เกิดข้อผิดพลาด: " + response);
            }
        },        
          error: function() {
              alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
          }
      });
  });
});

window.openCity = function(evt, choice) {
  var i, tabcontent, tablinks;
  if (document.getElementById(choice).style.display === "block") {
    return;
  }
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
    tablinks[i].style.cursor = "pointer";
  }
  document.getElementById(choice).style.display = "block";
  evt.currentTarget.style.cursor = "default";
  evt.currentTarget.className += " active";
}

const acc = document.getElementsByClassName("accordion");
for (let i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function () {
    for (let j = 0; j < acc.length; j++) {
      if (acc[j] !== this) {
        acc[j].classList.remove("active");
        const panel = acc[j].nextElementSibling;
        panel.style.maxHeight = null;
        panel.style.paddingTop = "0";
        panel.style.paddingBottom = "0";
      }
    }
    this.classList.toggle("active");
    const panel = this.nextElementSibling;

    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
      panel.style.paddingTop = "0";
      panel.style.paddingBottom = "0";
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
      panel.style.paddingTop = "10px";
      panel.style.paddingBottom = "10px";
    }
  });
}

window.toggleDropdown = function() {
  const dropdown = document.getElementById('dropdown');
  dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

window.onclick = function(event) {
  if (!event.target.matches('.admin')) {
      var dropdown = document.getElementById("dropdown");
      if (dropdown && dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
      }
  }
}

window.toggleBuildingNumber = function() {
  const buildingNumberField = document.getElementById("buildingNumberField");
  buildingNumberField.style.display = hasBuildingNumber.checked ? "inline-block" : "none";

}

window.onload = function () {
  document.getElementById("btnAdd").click();
};

window.toggleBuildingFields = function() {
  const selected = document.getElementById('existing_building').value;
  const buildingFields = document.getElementById('manualBuildingFields');

  if (selected) {
      buildingFields.style.display = 'none';
      const [number, name, lat, lng] = selected.split('|');
      document.getElementById('buildingnumber').value = number;
      document.getElementById('building').value = name;
      document.getElementById('lat').value = lat;
      document.getElementById('lng').value = lng;
  } else {
      buildingFields.style.display = 'block';
      document.getElementById('buildingnumber').value = '';
      document.getElementById('building').value = '';
      document.getElementById('lat').value = '';
      document.getElementById('lng').value = '';
  }
}
