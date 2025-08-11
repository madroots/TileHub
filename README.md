
![tilehub_banner](https://github.com/user-attachments/assets/4550871c-0d47-4507-bc59-87ac672ae316)

# TileHub

TileHub is a simple and customizable dashboard application that allows you to manage and display tiles with URLs and icons.

## ⭐ Features

- **Tile Management**: Add, edit, and delete tiles
- **Group Organization**: Organize tiles into groups
- **Icon Upload**: Upload custom icons for tiles (SVG supported)
- **Responsive Design**: Viewable on various devices
- **Drag & Drop Reordering**: Easily reorder tiles and groups
- **Group Management**: Rename or delete groups
- **Discreet Edit Access**: Access edit mode via `?edit=true` parameter (gear icon can be hidden)

## 🚩 Known Issues / To Do

- *No major issues currently reported.*

> [!WARNING]  
> **No Authentication**: Designed for trusted home networks. TileHub omits authentication to keep things simple.

## 🛠️ Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/madroots/TileHub.git && cd TileHub
   ```

2. **Edit Database Password**

   Open `.env` with your preferred text editor:
   
   ```bash
   nano .env
   ```
   
   Change the database password to your own.

3. **Run**

   ```bash
   docker-compose up -d --build
   ```

App runs on `localhost:5200` unless you changed the port in `docker-compose.yml` 🥳
