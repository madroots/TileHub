
![tilehub_banner](https://github.com/user-attachments/assets/4550871c-0d47-4507-bc59-87ac672ae316)

# TileHub

TileHub is a simple and customizable dashboard application that allows you to manage and display tiles with URLs and icons.
Works best for new browser tab with an extension like Custom New Tab URL Options or similar. 

## ⭐ Features

- **Tile Management**: Add, edit, delete and drag and drop tiles to organize
- **Icon Upload**: Upload custom icons for tiles (SVG supported)
- **Responsive Design**: Looking good on mobile and desktop
- **Group Management**: Add, rename, delete and drag and drop groups
- **Discreet Edit Access**: Access edit mode via `?edit=true` parameter (gear icon can be hidden)

## 🎥 Demo video

https://github.com/user-attachments/assets/eb97a334-caba-4e45-ba92-734e7eb5fbd1

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
   
   Change the database password to your own (or don't)

3. **Run**

   ```bash
   docker-compose up -d --build
   ```

App runs on `localhost:5200` unless you changed the port in `docker-compose.yml` 🥳
