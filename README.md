
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

### Option 1: Using Docker Compose (Recommended)

Simply copy the following docker-compose.yml content to a file and run:

```yaml
version: '3.8'

services:
  tilehub-app:
    image: madroots/tilehub-app:latest
    depends_on:
      - tilehub-db
    environment:
      - DB_HOST=tilehub-db
      - DB_NAME=tilehubdb
      - DB_USER=tilehubuser
      - DB_PASS=tilehubpass
    networks:
      - tilehub-network

  tilehub-web:
    image: madroots/tilehub-nginx:latest
    ports:
      - "5200:80"
    depends_on:
      - tilehub-app
    networks:
      - tilehub-network

  tilehub-db:
    image: madroots/tilehub-mariadb:latest
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: tilehubdb
      MYSQL_USER: tilehubuser
      MYSQL_PASSWORD: tilehubpass
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - tilehub-network

volumes:
  db_data:

networks:
  tilehub-network:
    driver: bridge
```

Then run:
```bash
docker-compose up -d
```

Or download it directly:
```bash
wget https://raw.githubusercontent.com/madroots/TileHub/main/docker-compose.yml
docker-compose up -d
```

App runs on `localhost:5200` unless you changed the port in `docker-compose.yml` 🥳

### Option 2: For Developers (Building from Source)

If you want to build the images from source:

```bash
git clone https://github.com/madroots/TileHub.git && cd TileHub
docker-compose -f docker-compose.dev.yml up -d --build
```

## 📌 FAQ

**Q: Why TileHub over something else?**  
A: I like to keep things simple. Click and done. With lots of opensource projects these days, Its hard to keep stuff in our heads. You don't need to remember anything with TileHub - its made to be straightfoward and simple to setup. Think of it as your personal "start page" with no learning curve.

---

**Q: Is there any authentication or login system?**  
A: Not at the moment. Anyone who can access your TileHub instance can edit tiles if they know the `?edit=true` URL parameter. If you need security, consider running TileHub behind a reverse proxy with HTTP authentication or limiting access via your network/firewall. Auth can be added at any point in the future if requested though.

---

**Q: How do I enter edit mode?**  
A: Add `?edit=true` to the end of your TileHub URL (e.g., `http://localhost:5200/?edit=true`). This will enable tile and group editing options. Or use Settings button if you enabled it.

---

**Q: Can I back up my tiles?**  
A: Not yet, but export and import is planned!

---

**Q: Does TileHub work on mobile devices?**  
A: Of course. It reminds Link Tree-like app on mobile.

---

**Q: Is there a dark mode?**  
A: You better sit down now: There is ONLY dark mode. We don't like light in here.
