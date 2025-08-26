
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

### Option 1: Using Docker Compose (Recommended for development)

1. **Clone the Repository**

   ```bash
   git clone https://github.com/madroots/TileHub.git && cd TileHub
   ```

2. **Run**

   ```bash
   docker-compose up -d --build
   ```

### Option 2: Using Pre-built Docker Images (Recommended for production)

1. **Download the docker-compose.user.yml file**

   ```bash
   curl -O https://raw.githubusercontent.com/madroots/TileHub/main/docker-compose.user.yml
   curl -O https://raw.githubusercontent.com/madroots/TileHub/main/app.zip
   unzip app.zip
   ```

2. **Run**

   ```bash
   docker-compose -f docker-compose.user.yml up -d
   ```

App runs on `localhost:5200` unless you changed the port in `docker-compose.yml` 🥳

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
