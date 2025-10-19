# TV Watchlist Database Persistence Guide

## ✅ What's Fixed

Your TV Watchlist application now has **proper data persistence** configured with the following improvements:

### 1. Correct MongoDB Volume Mounting
- **Fixed**: Volume now mounts to `/data/db` (correct MongoDB data directory)
- **Before**: Was mounted to `/var/lib/mongodb/data` (incorrect path)

### 2. Restart Policies Added
- All containers now have `restart: unless-stopped`
- Containers will automatically restart after system reboots
- Containers will restart if they crash

### 3. Enhanced MongoDB Configuration
- Added authentication (`--auth`)
- Proper network binding (`--bind_ip_all`)
- External port mapping for direct access (27017)

### 4. Backup & Recovery System
- **`./backup-db.sh`** - Creates timestamped backups of your data
- **`./restore-db.sh`** - Restores data from backups
- **`./health-check.sh`** - Monitors system health and data integrity

## 🔄 Data Recovery (Current Situation)

**Your existing shows were lost** due to the incorrect volume mounting. However, the system is now properly configured to prevent future data loss.

To get your shows back, you'll need to:
1. Re-add your shows through the web interface, OR
2. Restore from a backup if you have one

## 📋 Maintenance Commands

### Daily Health Check
```bash
./health-check.sh
```

### Create Backup (Run before major changes)
```bash
./backup-db.sh
```

### Restore from Backup
```bash
# List available backups
ls -la ./backups/

# Restore specific backup
./restore-db.sh tvwatchlist_backup_YYYYMMDD_HHMMSS
```

### Manual Database Access
```bash
# Connect to MongoDB shell
docker exec -it database mongosh --username api --password password --authenticationDatabase admin api

# View collections
db.getCollectionNames()

# Count episodes
db.Episode.countDocuments()

# Count archived series
db.ArchivedSeries.countDocuments()
```

## 🔒 Data Safety Best Practices

1. **Regular Backups**: Run `./backup-db.sh` weekly or before major changes
2. **Monitor Health**: Run `./health-check.sh` after system restarts
3. **Volume Protection**: Never delete the `tvwatchlist_db_data` Docker volume
4. **Archive Feature**: Use the archive feature instead of deleting shows

## 📁 File Structure

```
tvwatchlist/
├── docker-compose.yaml     # ✅ Fixed with proper volume mounting & restart policies
├── backups/               # 📁 Backup storage directory
├── backup-db.sh          # 💾 Database backup script
├── restore-db.sh         # 🔄 Database restore script
└── health-check.sh       # 🏥 System health monitoring
```

## 🚀 Future-Proof Setup

Your application will now:
- ✅ Survive system restarts
- ✅ Survive container crashes  
- ✅ Persist data correctly
- ✅ Allow easy backups
- ✅ Provide health monitoring

## ⚠️ Important Notes

1. **Volume Location**: Your data is stored in Docker volume `tvwatchlist_db_data`
2. **Backup Location**: Backups are stored in `./backups/` directory
3. **Recovery**: If you lose data, check `./backups/` for recent backups
4. **System Restarts**: Containers will auto-start when Docker starts

## 🆘 Troubleshooting

### If containers don't start after reboot:
```bash
docker-compose up -d
```

### If data seems missing:
```bash
./health-check.sh
# Check episode count and collections
```

### If you need to reset everything:
```bash
docker-compose down
docker volume rm tvwatchlist_db_data
docker-compose up -d
```

Your TV Watchlist is now properly configured for data persistence! 🎉