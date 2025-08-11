
![tilehub_banner](https://github.com/user-attachments/assets/4550871c-0d47-4507-bc59-87ac672ae316)


# TileHub

TileHub is a simple and customizable dashboard application that allows you to manage and display tiles with URLs and icons.<br/>


## ⭐ Features

- **Tile Management**: Add, edit, and delete tiles.  
- **Group Organization**: Organize tiles into groups.  
- **Icon Upload**: Upload custom icons for tiles. SVG supported.
- **Responsive Design**: Viewable on various devices.  
- **Editable Mode**: Toggle between view and edit modes.
- **Drag & Drop Reordering**: Easily reorder tiles and groups with smooth drag and drop functionality.
- **Group Management**: Rename or delete groups directly from the interface.
- **Visual Edit Indicators**: Clear visual cues for edit mode with orange dotted borders around tiles.
- **Settings Panel**: Access settings through the gear icon for quick toggles.<br/>



## 🚩 Known Issues / To Do

- *No major issues currently reported.*<br/>

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
