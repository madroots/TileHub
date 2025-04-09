<p align="center">
  <img src="https://github.com/user-attachments/assets/47037d54-b23b-4b47-baa5-f26ced05624d" alt="TileHub Icon" width="100" />

# TileHub

TileHub is a simple and customizable dashboard application that allows you to manage and display tiles with URLs and icons.

## ⭐ Features

- **Tile Management**: Add, edit, and delete tiles.
- **Group Organization**: Organize tiles into groups.
- **Icon Upload**: Upload custom icons for tiles.
- **Responsive Design**: Viewable on various devices.
- **Editable Mode**: Toggle between view and edit modes.


## 🚩 Known Issues/To Do

- **Tile Ordering**: It is not possible to change order of Tiles from web UI yet. It can be changed inside DB though.


## 🛠️ Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/madroots/TileHub.git && cd TileHub

2. **Edit Database Password**

   Open docker-compose.yml with your prefered text editor:
   ```bash
   nano docker-compose.yml
   ```
   and edit environment variables:
   ```
   DB_PASS
   MARIADB_ROOT_PASSWORD
   MARIADB_PASSWORD
   ```
3. **Run**
   ```
   docker-compose up -d
   ```

Thats it. 🥳
