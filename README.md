# FilmVault — Group 11

CMP6210 Cloud Computing · Kaplan Bridging 2025 · Semester 1

A movie catalogue web application deployed on AWS. Users can browse a curated collection of 10 films, filter by genre, and view detailed information for each title.

---

## Architecture

| Layer | Service | Detail |
|---|---|---|
| Web servers | EC2 × 2 (Amazon Linux 2023) | Apache + PHP, spread across 2 AZs |
| Load balancer | Elastic Load Balancer | Health-checks every 30s, auto-routes on failure |
| Database | RDS MySQL 8.4 Multi-AZ | Private subnet, failover within 60–120s |
| Media | S3 + CloudFront CDN | Poster images cached at global edge locations |
| Audit logs | CloudTrail → S3 | All AWS API activity logged continuously |
| Monitoring | CloudWatch | Metrics + email alerts on threshold breach |
| Region | ap-southeast-2 (Sydney) | All resources |

All resources are prefixed `Group11_` per the assessment brief.

---

## Project Structure

```
├── index.php          Homepage — movie grid (FR1, FR2)
├── filter.php         Genre filter page (FR3, FR7)
├── movie.php          Movie detail page (FR4, FR5)
├── config/
│   └── db.php         Database connection (reads .env)
├── assets/
│   └── css/
│       └── style.css  Dark cinema theme
└── sql/
    ├── schema.sql     CREATE TABLE statements (run first)
    └── seed.sql       10 seeded movies (run after schema)
```

---

## Local Setup

### 1. Clone and configure

```bash
git clone https://github.com/mmmmlsy/Group_11_Cloud_Computing_Movie.git
cd Group_11_Cloud_Computing_Movie
cp .env.example .env
```

Edit `.env` with your RDS endpoint and CloudFront domain:

```
DB_HOST=your-rds-endpoint.ap-southeast-2.rds.amazonaws.com
DB_NAME=filmvault
DB_USER=admin
DB_PASS=your_password_here
DB_PORT=3306
CLOUDFRONT_URL=https://your-distribution-id.cloudfront.net
```

### 2. Provision the database

Connect to your RDS instance and run:

```bash
mysql -h <RDS_ENDPOINT> -u admin -p < sql/schema.sql
mysql -h <RDS_ENDPOINT> -u admin -p < sql/seed.sql
```

### 3. Upload poster images to S3

Upload JPG files named exactly as below to your S3 bucket (served via CloudFront):

```
shawshank.jpg      dark_knight.jpg    inception.jpg
godfather.jpg      pulp_fiction.jpg   interstellar.jpg
matrix.jpg         toy_story.jpg      forrest_gump.jpg
mad_max.jpg
```

### 4. Deploy to EC2

Place all application files (everything except `sql/` and `.env.example`) in `/var/www/html/` on each EC2 instance.

```bash
scp -r -i your-key.pem . ec2-user@<EC2_IP>:/var/www/html/
```

Copy your `.env` to each instance — **never commit `.env` to git**.

---

## Security Notes

- `.env` is git-ignored — never commit credentials
- RDS is in a private subnet; port 3306 open only to `Group11_WebServerSG`
- All DB queries use MySQLi prepared statements (SQL injection safe)
- All output rendered with `htmlspecialchars()` (XSS safe)

---

## Team

Group 11 · CMP6210 Cloud Computing · Kaplan Australia · 2025
