import subprocess
import re

# Données des utilisateurs
users = [
    ('Lilian Maulny', 'lilian.maulny@etu.univ-tours.fr', 'admin_password', True),
    ('Sophie Dupont', 'sophie.dupont@gmail.com', 'k8D$3pQ!7wVxZm@L', False),
    ('Thomas Martin', 'thomas.martin@outlook.fr', 'E2c#5NjR$9yTbP7!', False),
    ('Claire Lefebvre', 'claire.lefebvre@yahoo.fr', 'B6v*Fz8@pX3mKt!L', False),
    ('Nicolas Bernard', 'n.bernard@free.fr', 'W9q$Jp7&Zm4cRh@2', False),
    ('Marie Dubois', 'marie.dubois@orange.fr', 'P5s*Lb8!2DvYx@Nz', False),
    ('Pierre Moreau', 'pierre.moreau@laposte.net', 'K3b#Vn7$zP9cFs!M', False),
    ('Julie Lambert', 'julie.lambert@protonmail.com', 'T7g*Lp9@Qd3rZv!6', False),
    ('Alexandre Petit', 'a.petit@gmail.com', 'H4j$Fm8#3WxCz@7V', False),
    ('Camille Robert', 'camille.robert@outlook.com', 'S5b*Rt9!2QvYn@7J', False),
    ('Lucas Durand', 'lucas.durand@gmail.com', 'G8c$Lw6&3PzXb@5N', False),
    ('Emma Richard', 'emma.richard@yahoo.com', 'N9r*Kf7!4TvYm@2P', False),
    ('Antoine Simon', 'antoine.simon@free.fr', 'Z3d$Hp5#8CxVb@6M', False),
    ('Léa Michel', 'lea.michel@outlook.fr', 'F7k*Jt9!3BvXn@4R', False),
    ('Hugo Leroy', 'h.leroy@orange.fr', 'Q6c$Wm8&2NzPv@5T', False),
    ('Paul Bonjour', 'paul.bonjour@salut.fr', 'M4t$6Bn!8J0uR@9Y', False),
    ('Aurélie Marchand', 'aurelie.marchand@gmail.com', 'A7j*Rp5!9WvZk@3D', False),
    ('Mathieu Caron', 'm.caron@outlook.fr', 'C8b$Hs6#2TzXm@4V', False),
    ('Elodie Leclerc', 'elodie.leclerc@yahoo.fr', 'L5f*Dt7!3RvYn@8K', False),
    ('Julien Fournier', 'j.fournier@free.fr', 'J9g$Kw4&7PzXc@2B', False),
    ('Nathalie Girard', 'nathalie.girard@gmail.com', 'G6h*Mt3!8CvZb@5R', False),
    ('François Bonnet', 'francois.bonnet@laposte.net', 'B4j$Fs9#6NzPt@3W', False),
    ('Céline Morel', 'celine.morel@outlook.com', 'M7d*Rk5!2BvYc@9P', False),
    ('Guillaume Rousseau', 'g.rousseau@gmail.com', 'R3s$Hj8&7TzXf@4V', False),
    ('Isabelle Vincent', 'isabelle.vincent@orange.fr', 'V6g*Dz9!4PvYn@2J', False),
    ('Sébastien Rey', 'sebastien.rey@protonmail.com', 'S8b$Kw5#3CzXm@7T', False),
]
# Reset the file
with open("database/users.sql", "w") as f:
    f.write("")
# Générer le SQL
with open("database/users.sql", "w") as f:
    f.write("INSERT INTO users (username, email, password, is_admin) VALUES\n")

    for i, (username, email, password, is_admin) in enumerate(users):
        # Exécuter PHP pour hasher le mot de passe
        cmd = f"php -r 'echo password_hash(\"{password}\", PASSWORD_DEFAULT);'"
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
        hashed_password = result.stdout.strip()

        # Échapper les apostrophes dans le nom d'utilisateur
        username = username.replace("'", "''")

        # Ajouter l'entrée SQL
        f.write(f"('{username}', '{email}', '{hashed_password}', {'TRUE' if is_admin else 'FALSE'})")

        if i < len(users) - 1:
            f.write(",\n")
        else:
            f.write(";\n")

print("SQL généré dans le fichier 'database/users.sql'")