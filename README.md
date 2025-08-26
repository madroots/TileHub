
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

### Option 2: Using Pre-built Docker Image (Recommended for production)

You can use the pre-built Docker image from Docker Hub:

```bash
# Pull the latest image
docker pull yourdockerhubusername/tilehub:latest

# Run with docker-compose using the pre-built image
docker-compose up -d
```

### Automated Builds with GitHub Actions

This repository is configured with GitHub Actions to automatically build and push Docker images to Docker Hub when changes are pushed to the main branch or when new tags are created.

To set up automated builds:

1. Fork this repository or push it to your own GitHub account
2. Create a Docker Hub account if you don't have one
3. Create a repository on Docker Hub (e.g., `tilehub`)
4. In your GitHub repository settings, add the following secrets:
   - `DOCKERHUB_USERNAME` - Your Docker Hub username
   - `DOCKERHUB_TOKEN` - Docker Hub access token (generate one in Docker Hub account settings)
   - `IMAGE_NAME` - The name of your Docker Hub repository (e.g., `tilehub`)
5. Push changes to your repository to trigger the first build

Images will be tagged as follows:
- `latest` for pushes to the main branch
- Version numbers for git tags (e.g., `v1.0.0` creates tags `1.0.0`, `1.0`, and `latest`)
- Commit SHA for other branches

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
