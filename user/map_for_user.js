import { getRoutes } from "./user_controller_js/get_routes.js";
import {
  clearPolylines,
  clearInfoWindows,
} from "./user_controller_js/decode_and_draw_polyline.js";
import { clearSelectPath } from "./user_controller_js/show_custom_route_info.js";

let map, marker, selectDestination, userLocation;
let lastUsedLoc = null;

async function initMap() {
  const position = { lat: 14.98747028934542, lng: 102.11796446410003 };

  const checkbox = document.getElementById('toggleSwitch');

checkbox.addEventListener('change', () => {
  if (checkbox.checked) {
    document.getElementById('handleText').textContent = 'ข้อมูลหน่วยงาน';
    document.getElementById('displayTap').style.display = 'none';
    document.getElementById('displayData').style.display = 'block';
  } else { 
    document.getElementById('handleText').textContent = 'เส้นทาง';
    document.getElementById('displayTap').style.display = 'block';
    document.getElementById('displayData').style.display = 'none';
  }
});

  var myStyles = [
    {
      featureType: "poi",
      elementType: "labels",
      stylers: [{ visibility: "off" }],
    },
  ];

  var myOptions = {
    zoom: 17,
    center: position,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    styles: myStyles,
    clickableIcons: false,
  };

  map = new google.maps.Map(document.getElementById("map"), myOptions);
  marker = new google.maps.Marker({
    map: map,
    position: position,
    animation: google.maps.Animation.DROP,
    icon: {
      url: "../images/rmuti.png",
      scaledSize: new google.maps.Size(20, 40),
      anchor: new google.maps.Point(10, 40),
    },
  });

  const response = await fetch("user/user_controller_php/department_position.php");
  const data = await response.json();
  const locations = data.locations;
  const departments = data.departments;
  
  const defaultIcon = {
    url: "https://cdn-icons-png.flaticon.com/128/7945/7945007.png",
    scaledSize: new google.maps.Size(32, 32),
  };
  const selectedIcon = {
    url: "https://cdn-icons-png.flaticon.com/128/9131/9131546.png",
    scaledSize: new google.maps.Size(32, 32),
  };
  
  let activeMarker = null;
  const createdPositions = new Set();
  const markersByBuildingName = [];
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
    markersByBuildingName[loc.building_name] = markerDestination;
    new CustomLabelOverlay(
      new google.maps.LatLng(lat, lng),
      loc.building_name,
      map,
      false
    );
  
    markerDestination.addListener("click", () => {
      if (document.getElementById("Back").style.display === "block") {
        return;
      } else {
      openSearchPanel();
      checkbox.checked = true;
      checkbox.dispatchEvent(new Event('change'));
      lastUsedLoc = loc;
      if (activeMarker) {
        activeMarker.setIcon(defaultIcon);
      }
      markerDestination.setIcon(selectedIcon);
      selectDestination = {
        lat: markerDestination.getPosition().lat(),
        lng: markerDestination.getPosition().lng(),
      };
      console.log(selectDestination);
      map.panTo(markerDestination.getPosition());
      activeMarker = markerDestination;
      getRoutes(selectDestination, map, userLocation);
      
      const displayDiv = document.getElementById("displayData");
      if (checkbox.checked) {
        displayDiv.style.display = "block";
      }
      showBuildingDepartments(loc);
    }
    });
    map.addListener("click", () => {
      if (document.getElementById("Back").style.display === "block") {
        goBack();
      } else {
      resetTabs();
      checkbox.checked = false;
      checkbox.dispatchEvent(new Event('change'));
      if (activeMarker) {
        activeMarker.setIcon(defaultIcon);
        activeMarker = null;
      }
      clearPolylines();
      clearInfoWindows();
      clearSelectPath();
      lastUsedLoc = null;
  }});
  });  

  const text = [
    {
      position: position,
      text: "มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน (สุรนารายณ์)",
      isImportant: true,
    },
  ];

  text.forEach(({ position, text, isImportant }) => {
    new CustomLabelOverlay(position, text, map, isImportant);
  });
  userLocation = await getUserLocation();
  async function getUserLocation() {
    return new Promise((resolve, reject) => {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            resolve({
              latitude: position.coords.latitude,
              longitude: position.coords.longitude,
            });
          },
          (error) => {
            console.error("ไม่สามารถดึงตำแหน่งที่ตั้งของผู้ใช้ได้:", error);
            reject(error);
          }
        );
      } else {
        console.error("เบราว์เซอร์ไม่รองรับ Geolocation API");
        reject("Geolocation not supported");
      }
    });
  }
  document
    .getElementById("resetLocationBtn")
    .addEventListener("click", async function () {
      try {
        if (activeMarker) {
          userLocation = {
            latitude: 14.98747028934542,
            longitude: 102.11796446410003,
          };
          const pos = activeMarker.getPosition();
          selectDestination = { lat: pos.lat(), lng: pos.lng() };
          clearPolylines();
          clearInfoWindows();
          clearSelectPath();
          await getRoutes(selectDestination, map, userLocation);
      } else {
        return;
      }
      } catch (error) {
        console.log("ไม่สามารถรีเซ็ตตำแหน่งของคุณได้ กรุณาลองใหม่อีกครั้ง");
      }
    });
    window.showBuildingDepartments = function(loc) {
      const displayDiv = document.getElementById("displayData");
    
      const buildingDepartments = departments.filter(
        (d) => d.building === loc.building_number
      );
    
      let title = "";
      if (loc.building_number > 500) {
        title = `อาคาร${loc.building_name}`;
      } else {
        title = `อาคาร ${loc.building_number} ${loc.building_name}`;
      }
      let html = `<h3>${title}</h3><ul>`;
        const parents = [];
        const groups = [];
        const childrenMap = {};
    
        buildingDepartments.forEach((dep) => {
          if (!dep.subordinate_to) {
            parents.push(dep);
          } else if (dep.subordinate_to === "สำนัก" || dep.subordinate_to === "สถาบัน" || dep.subordinate_to === "กอง" || 
            dep.subordinate_to === "ศูนย์" || dep.subordinate_to === "หน่วยงานอื่น ๆ") {
            groups.push(dep);
          } else {
            if (!childrenMap[dep.subordinate_to]) {
              childrenMap[dep.subordinate_to] = [];
            }
            const parent = departments.find((d) => d.name_th === dep.subordinate_to);
            if (parent && parent.building !== loc.building_number && 
              !parents.some(p => p.name_th === parent.name_th)) {
              parents.push(parent);
            }
            childrenMap[dep.subordinate_to].push(dep);
          }
        });
        parents.forEach((parent) => {
          const safeParentName = encodeURIComponent(parent.name_th);
          if (parent.building === loc.building_number) {
          html += `<li data-name="${safeParentName}"><strong>${parent.name_th}</strong>`;
          const children = childrenMap[parent.name_th];
          if (children && children.length > 0) {
            html += `<ul>`;
            children.forEach((child) => {
              const safeChildName = encodeURIComponent(child.name_th);
              html += `<li data-name="${safeChildName}"><strong>${child.name_th}</strong></li>`;
            });
            html += `</ul>`;
          }
          html += `</li>`;
        } else {
          let buildingLabel;
          if (Number(parent.building) > 500) {
            buildingLabel = `อาคาร${parent.building_name}`;
          } else {
            buildingLabel = `อาคาร ${parent.building}`;
          }          

          html += `<li><span>${parent.name_th} (${buildingLabel})</span>`;

          const children = childrenMap[parent.name_th];
          if (children && children.length > 0) {
            html += `<ul>`;
            children.forEach((child) => {
              const safeChildName = encodeURIComponent(child.name_th);
              html += `<li data-name="${safeChildName}"><strong>${child.name_th}</strong></li>`;
            });
            html += `</ul>`;
          }
          html += `</li>`;
        }
        });
        groups.forEach((group) => {
          const safeParentName = encodeURIComponent(group.name_th);
          html += `<li data-name="${safeParentName}"><strong>${group.name_th}</strong>`;
          html += `</li>`;
        });
    
        html += `</ul>`;
        displayDiv.innerHTML = html;
      document.getElementById('displayData').addEventListener('click', function(event) {
        let target = event.target;
        while (target && target !== this && !target.hasAttribute('data-name')) {
          target = target.parentElement;
        }
        if (target && target.hasAttribute('data-name')) {
          const name = decodeURIComponent(target.getAttribute('data-name'));
          showData(name);
        }
      });
    };
    document.getElementById('agency').addEventListener('input', function() {
      const query = this.value.trim();
      const resultsContainer = document.getElementById('search_result');
    
      if (query.length === 0) {
        resultsContainer.innerHTML = '';
        return;
      }
    
      fetch('user/user_controller_php/fuzzy_matching.php?q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
          if (!data.length) {
            resultsContainer.innerHTML = '<div style="padding:12px; color:#555;">ไม่พบข้อมูลที่ตรงกัน</div>';
            return;
          }
    
          resultsContainer.innerHTML = data.map(item => `
            <div class="search_result-item" 
                 data-type="${item.type}"
                 data-id="${item.id}"
                 data-service-name="${item.type === 'service' ? item.department_name || '' : item.name}" 
                 data-building-name="${item.building_name || ''}">
              <div class="search_result-service-name">${item.name}</div>
            </div>
          `).join('');          
          
          document.querySelectorAll('.search_result-item').forEach(el => {
            el.addEventListener('click', () => {
              const selectedName = el.getAttribute('data-service-name');
              const type = el.getAttribute('data-type');
              const id = el.getAttribute('data-id');
              const buildingName = el.getAttribute('data-building-name');

              fetch('user/user_controller_php/search_log.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                  user_input: query,
                  type: type,
                  id: id,
                  selected: 1
                })
              }).then(res => res.json())
              .then(resData => {
                if (resData.status !== 'success') {
                  console.error('บันทึกไม่สำเร็จ (selected):', resData.message);
                }
              });
              data.forEach(item => {
                if (item.id != id) {
                  fetch('user/user_controller_php/search_log.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                      user_input: query,
                      type: item.type,
                      id: item.id,
                      selected: 0
                    })
                  }).then(res => res.json())
                .then(resData => {
                  if (resData.status !== 'success') {
                    console.error('บันทึกไม่สำเร็จ (unselected):', resData.message);
                  }
                });
              }
            });
    
              document.getElementById('agency').value = '';
              resultsContainer.innerHTML = '';
    
              if (buildingName && markersByBuildingName[buildingName]) {
                google.maps.event.trigger(markersByBuildingName[buildingName], 'click');
                showData(selectedName);
              } else {
                console.log('ไม่พบหมุดสำหรับอาคาร:', buildingName);
              }
            });
          });          
        })
        .catch(err => {
          resultsContainer.innerHTML = '<div style="padding:12px; color:red;">เกิดข้อผิดพลาดในการค้นหา</div>';
          console.error(err);
        });
    });
}
initMap();

window.openCity = function (evt, choice) {
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
};

window.resetTabs = function() {
  var tabcontent = document.getElementsByClassName("tabcontent");
  for (var i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  var tablinks = document.getElementsByClassName("tablinks");
  for (var i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
    tablinks[i].style.cursor = "pointer";
  }
}


window.toggleDropdown = function () {
  const dropdown = document.getElementById("dropdown");
  dropdown.style.display =
    dropdown.style.display === "block" ? "none" : "block";
};

window.onclick = function (event) {
  if (!event.target.matches(".admin")) {
    var dropdown = document.getElementById("dropdown");
    if (dropdown && dropdown.style.display === "block") {
      dropdown.style.display = "none";
    }
  }
};

const toggleBtn = document.getElementById('toggleSearch');
const searchPanel = document.getElementById('search');
const toggleIcon = document.getElementById('toggleIcon');

function toggleSearchPanel() {
  const isHidden = searchPanel.classList.contains('hidden');
  
  searchPanel.classList.toggle('hidden');
  toggleBtn.classList.toggle('hidden');

  if (!isHidden) {
    toggleIcon.classList.remove('fa-search');
    toggleIcon.classList.add('fa-times');
  } else {
    toggleIcon.classList.remove('fa-times');
    toggleIcon.classList.add('fa-search');
  }
}

function openSearchPanel() {
  if (!searchPanel.classList.contains('hidden')) {
    toggleSearchPanel();
  }
}

function closeSearchPanel() {
  if (searchPanel.classList.contains('hidden')) {
    searchPanel.classList.remove('hidden');
    toggleBtn.classList.remove('hidden');
    toggleIcon.classList.remove('fa-times');
    toggleIcon.classList.add('fa-search');
  }
}

toggleBtn.addEventListener('click', toggleSearchPanel);

window.showData = function(name) {
  document.getElementById('titleSearch').style.display = 'none';
  document.getElementById('agency').style.display = 'none';
  document.getElementById('Back').style.display = 'block';
  document.getElementById('displayImage').style.display = 'block';

  fetch(`user/user_controller_php/department_data.php?name=${encodeURIComponent(name)}`)
    .then(response => response.json())
    .then(data => {
      document.getElementById('agencyName').innerHTML = data.name_html;
      document.getElementById('agencyName').style.display = 'block';
      document.getElementById('displayData').innerHTML = data.detail_html;
      document.getElementById('displayImage').innerHTML = data.image_html;
      showSlides(slideIndex);

      const row = document.querySelector(".row");
      
      row.addEventListener("wheel", function (e) {
        if (e.deltaY !== 0) {
          e.preventDefault();
          row.scrollLeft += e.deltaY;
        }
      }, { passive: false });
      let isDown = false;
      let startX;
      let scrollLeft;

      row.addEventListener("mousedown", (e) => {
        isDown = true;
        row.classList.add("dragging");
        startX = e.pageX - row.offsetLeft;
        scrollLeft = row.scrollLeft;
      });

      row.addEventListener("mouseleave", () => {
        isDown = false;
        row.classList.remove("dragging");
      });

      row.addEventListener("mouseup", () => {
        isDown = false;
        row.classList.remove("dragging");
      });

      row.addEventListener("mousemove", (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - row.offsetLeft;
        const walk = (x - startX) * 1;
        row.scrollLeft = scrollLeft - walk;
      });
      row.addEventListener("touchstart", (e) => {
        isDown = true;
        startX = e.touches[0].pageX - row.offsetLeft;
        scrollLeft = row.scrollLeft;
      }, { passive: true });
      
      row.addEventListener("touchend", () => {
        isDown = false;
      });
      
      row.addEventListener("touchmove", (e) => {
        if (!isDown) return;
        const x = e.touches[0].pageX - row.offsetLeft;
        const walk = (x - startX) * 1;
        row.scrollLeft = scrollLeft - walk;
      });
    })
    .catch(error => {
      console.error('เกิดข้อผิดพลาดในการโหลดข้อมูล:', error);
    });
}

window.copyText = function(el) {
  const text = el.innerText;
  navigator.clipboard.writeText(text).then(() => {
    showToast("คัดลอกแล้ว: " + text);
  });
}

window.showToast = function(message) {
  const toast = document.getElementById("toast");
  toast.innerText = message;
  toast.style.visibility = "visible";
  toast.style.opacity = "1";
  toast.style.bottom = "50px";

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.bottom = "30px";
    setTimeout(() => {
      toast.style.visibility = "hidden";
    }, 500);
  }, 2000);
}

window.goBack = function() {
  document.getElementById('Back').style.display = 'none';
  document.getElementById('titleSearch').style.display = 'block';
  document.getElementById('agency').style.display = 'block';
  document.getElementById('agencyName').style.display = 'none';
  document.getElementById('displayData').innerHTML = '<p>กรูณาเลือกหมุดปลายทาง</p>';
  document.getElementById('displayImage').innerHTML = '';

  showBuildingDepartments(lastUsedLoc);
}

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('accordion')) {
        const panel = e.target.nextElementSibling;
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
    } else if (e.target && e.target.classList.contains('accordion-service')) {
      const panel = e.target.nextElementSibling;
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
        panel.style.marginBottom = (panel.style.display === 'block') ? '10px' : '0px';
    }
});

let slideIndex = 1;

window.plusSlides = function(n) {
  showSlides(slideIndex += n);
}
window.currentSlide = function(n) {
  showSlides(slideIndex = n);
}

window.showSlides = function(n) {
  const slides = document.getElementsByClassName("mySlides");
  const dots = document.getElementsByClassName("imgslide");
  const captionText = document.getElementById("caption");

  if (n > slides.length) { slideIndex = 1; }
  else if (n < 1) { slideIndex = slides.length; }
  else { slideIndex = n; }

  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }

  for (let i = 0; i < dots.length; i++) {
    dots[i].classList.remove("active");
  }

  if(slides.length > 0) slides[slideIndex - 1].style.display = "block";
  if(dots.length > 0) {
    dots[slideIndex - 1].classList.add("active");
    if(captionText) captionText.innerHTML = dots[slideIndex - 1].alt || '';
  }
};
