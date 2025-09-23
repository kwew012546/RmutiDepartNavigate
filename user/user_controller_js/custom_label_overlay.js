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
        width: 15%;
        font-size: 18px;
        color: #FF7100;
        margin-left: -60px;
        margin-top: 30px;
        font-weight: bold;
        text-align: center;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        textAlign: center;
      `;
      } else {
        this.div.style.cssText = `
        position: absolute;
        width: 10%;
        margin-left: 65px;
        font-size: 14px;
        color: black;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
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