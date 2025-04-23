import subprocess
import re

# Données des utilisateurs
users = [
    ('Lilian Maulny', 'lilian.maulny@etu.univ-tours.fr', 'admin_password', True),
    ('Sophie Dupont', 'sophie.dupont@gmail.com', 'Cinnamon42$', False),
    ('Thomas Martin', 'thomas.martin@outlook.fr', 'EchoZeal!9', False),
    ('Claire Lefebvre', 'claire.lefebvre@yahoo.fr', 'SilentDusk88', False),
    ('Nicolas Bernard', 'n.bernard@free.fr', 'Obsidian_73', False),
    ('Marie Dubois', 'marie.dubois@orange.fr', 'VelvetFox_5', False),
    ('Pierre Moreau', 'pierre.moreau@laposte.net', 'Nocturne84!', False),
    ('Julie Lambert', 'julie.lambert@protonmail.com', 'QuartzBee#3', False),
    ('Alexandre Petit', 'a.petit@gmail.com', 'TwistRay92', False),
    ('Camille Robert', 'camille.robert@outlook.com', 'NovaFern_17', False),
    ('Lucas Durand', 'lucas.durand@gmail.com', 'ZetaDrive$1', False),
    ('Emma Richard', 'emma.richard@yahoo.com', 'SableEcho23', False),
    ('Antoine Simon', 'antoine.simon@free.fr', 'SolarNest8!', False),
    ('Léa Michel', 'lea.michel@outlook.fr', 'FrostNote7', False),
    ('Hugo Leroy', 'h.leroy@orange.fr', 'IndigoJump_6', False),
    ('Paul Bonjour', 'paul.bonjour@salut.fr', 'EchoLeaf88', False),
    ('Aurélie Marchand', 'aurelie.marchand@gmail.com', 'DawnRiddle5!', False),
    ('Mathieu Caron', 'm.caron@outlook.fr', 'CrimsonFog21', False),
    ('Elodie Leclerc', 'elodie.leclerc@yahoo.fr', 'VelcroMaze9', False),
    ('Julien Fournier', 'j.fournier@free.fr', 'NightLoom77', False),
    ('Nathalie Girard', 'nathalie.girard@gmail.com', 'TwilightGem6$', False),
    ('François Bonnet', 'francois.bonnet@laposte.net', 'DustRaven42!', False),
    ('Céline Morel', 'celine.morel@outlook.com', 'SageNova_3', False),
    ('Guillaume Rousseau', 'g.rousseau@gmail.com', 'DriftChord91', False),
    ('Isabelle Vincent', 'isabelle.vincent@orange.fr', 'MoonGlint7$', False),
    ('Sébastien Rey', 'sebastien.rey@protonmail.com', 'RuneFlash55!', False),
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