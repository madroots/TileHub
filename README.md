<img src="https://github.com/user-attachments/assets/47037d54-b23b-4b47-baa5-f26ced05624d" alt="TileHub_Icon" width="100" />
# TileHub

TileHub is a simple and customizable dashboard application that allows you to manage and display tiles with URLs and icons.

## ⭐ Features

- **Tile Management**: Add, edit, and delete tiles.
- **Group Organization**: Organize tiles into groups.
- **Icon Upload**: Upload custom icons for tiles.
- **Responsive Design**: Viewable on various devices.
- **Editable Mode**: Toggle between view and edit modes.


## 🚩 Known Issues/To Do

- **No Authentication**: App is intended to be run on a home network with no public access. I decided to leave authentication out and keep it simple since max damage that can be done is that someone will mess up your dashboard lol. This might be reconsidered in furure though.
- **Tile Ordering**: It is not possible to change order of Tiles from web UI yet. It can be changed inside DB though.
- **Group Organization**: Groups cannot be added/removed from web UI yet. You can make new group by creating Tile, assigning to existing group and renaming the group in db. Group will become available then.


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
