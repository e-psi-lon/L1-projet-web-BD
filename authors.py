import os
import base64
import re

# Configuration
image_dir = 'images/authors/'  # Dossier contenant vos images
authors_sql_file = 'database/authors.sql'  # Fichier SQL existant
output_sql_file = 'database/authors_images.sql'  # Fichier SQL résultant

def extract_url_names_from_sql(sql_file):
    """Extrait les url_names du fichier SQL existant."""
    with open(sql_file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Recherche des url_names dans le fichier SQL
    pattern = r"'([^']+)', '([^']+)',"
    matches = re.findall(pattern, content)
    return {url_name: name for name, url_name in matches}

def create_image_update_sql(image_files, url_names):
    """Crée les instructions UPDATE pour chaque image."""
    updates = []

    for file_name in image_files:
        # Récupère le nom de base du fichier (sans l'extension)
        base_name = os.path.splitext(file_name)[0]

        # Vérifie si ce nom correspond à un url_name d'auteur
        if base_name in url_names:
            file_path = os.path.join(image_dir, file_name)
            file_size = os.path.getsize(file_path)

            # Vérifie la taille du fichier (max 2MB)
            if file_size <= 2 * 1024 * 1024:
                # Lire l'image et l'encoder en base64
                with open(file_path, 'rb') as img_file:
                    image_data = img_file.read()
                    base64_data = base64.b64encode(image_data).decode('utf-8')

                # Créer l'instruction UPDATE
                update_sql = f"UPDATE authors SET image = FROM_BASE64('{base64_data}') WHERE url_name = '{base_name}';"
                updates.append(update_sql)
                print(f"Image traitée: {file_name} - {file_size/1024:.2f} KB")
            else:
                print(f"ATTENTION: {file_name} dépasse 2MB ({file_size/1024/1024:.2f} MB)")

    return updates

def main():
    # Vérifier l'existence du dossier d'images
    if not os.path.exists(image_dir):
        print(f"Erreur: Le dossier {image_dir} n'existe pas")
        return

    # Récupérer la liste des fichiers d'images
    image_files = [f for f in os.listdir(image_dir) if os.path.isfile(os.path.join(image_dir, f))]

    # Extraire les url_names du fichier SQL
    url_names = extract_url_names_from_sql(authors_sql_file)

    # Créer les instructions UPDATE
    update_statements = create_image_update_sql(image_files, url_names)

    # Écrire les instructions dans le fichier de sortie
    with open(output_sql_file, 'w', encoding='utf-8') as f:
        f.write("-- Instructions for images\n\n")
        for statement in update_statements:
            f.write(statement + "\n\n")

    print(f"{len(update_statements)} instructions UPDATE générées dans {output_sql_file}")

if __name__ == "__main__":
    main()