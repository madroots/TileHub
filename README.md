<p align="center">
  <img src="https://github.com/user-attachments/assets/47037d54-b23b-4b47-baa5-f26ced05624d" alt="TileHub Icon" width="100" />
</p>

# TileHub

TileHub is a simple and customizable dashboard application that allows you to manage and display tiles with URLs and icons.<br/>

![Screenshot_2025-04-09_21-25-48](https://github.com/user-attachments/assets/14675089-b313-46e1-90b0-1f0eb5df7ab8)<br/>


## ⭐ Features

- **Tile Management**: Add, edit, and delete tiles.  
- **Group Organization**: Organize tiles into groups.  
- **Icon Upload**: Upload custom icons for tiles. SVG supported.
- **Responsive Design**: Viewable on various devices.  
- **Editable Mode**: Toggle between view and edit modes.<br/>



## 🚩 Known Issues / To Do

- **Tile Ordering**: It is not possible to change order of Tiles from web UI yet. It can be changed inside DB though.<br/>

> [!WARNING]  
> **No Authentication**: Designed for trusted home networks, TileHub omits authentication to keep things simple. Since the risk is minimal, this was a deliberate choice, though future support may be added.<br/>



## 🛠️ Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/madroots/TileHub.git && cd TileHub

2. **Edit Database Password**

   Open .env with your prefered text editor:
   ```bash
   nano .env
   ```
   and change database password to your own

3. **Run**
   ```
   docker-compose up -d --build
   ```

App runs on `localhost:5200` unless you changed port in docker-compose.yml 🥳
