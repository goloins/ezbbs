-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    theme VARCHAR(50) DEFAULT 'default',
    isadmin BOOLEAN DEFAULT false,
    defaultlocation VARCHAR(255) DEFAULT 'Somewhere on the internet',
    defaultbio TEXT DEFAULT 'This user prefers to keep an air of mystery about them.',
    defaultavatar VARCHAR(255) DEFAULT 'assets/png/default_avatar.png',
    awards JSON DEFAULT '[]',
    isbanned BOOLEAN DEFAULT false,
    ismoderator JSON DEFAULT '[]',
    defaultsignature TEXT DEFAULT '',
    sigbanners JSON DEFAULT '[]',
    userportrait VARCHAR(255) DEFAULT 'assets/png/blank_portrait.png',
    usernamecolor VARCHAR(7) DEFAULT '#000000',
    usernamestyle VARCHAR(20) DEFAULT 'normal',
    joindate INT DEFAULT 0,
    crackedportrait BOOLEAN DEFAULT false,
    duncecorner BOOLEAN DEFAULT false,
    userkudos INT DEFAULT 0,
    userkudostogive INT DEFAULT 1,
    userposts INT DEFAULT 0,
    userwebsite VARCHAR(255) DEFAULT '',
    usergemsite VARCHAR(255) DEFAULT '',
    userspacehey VARCHAR(255) DEFAULT '',
    userirchandleandnet VARCHAR(255) DEFAULT '',
    usersmsnescargot VARCHAR(255) DEFAULT '',
    profileprimarycolor VARCHAR(7) DEFAULT '#FFFFFF',
    profilesecondarycolor VARCHAR(7) DEFAULT '#CCCCCC',
    profileheadingtextcolor VARCHAR(7) DEFAULT '#000000',
    profilelowerheadingcolor VARCHAR(7) DEFAULT '#999999',
    profilehyperlinkcolor VARCHAR(7) DEFAULT '#0000FF'
);

-- Admin user with elevated permissions
INSERT INTO users (username, password, email, isadmin, joindate) VALUES 
('thedude', '$2y$10$example_hash_here_blah', 'admin@mysite.com', true, 1710777600);