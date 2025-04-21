# Petit script pour combiner les fichiers SQL
import os


current_directory = os.path.dirname(os.path.abspath(__file__))
database_directory = os.path.join(current_directory, "database")
output_file_path = os.path.join(database_directory, "combined.sql")

# Liste des fichiers SQL à combiner (dans l'ordre)
sql_files = [
    "schema.sql",
    "users.sql",
    "suggestions.sql",
    "authors.sql",
    "authors_images.sql",
    "books.sql",
    "content.sql",
]

# Combiner les fichiers SQL
with open(output_file_path, "w") as output_file:
    for sql_file in sql_files:
        sql_file_path = os.path.join(database_directory, sql_file)
        with open(sql_file_path, "r") as input_file:
            content = input_file.read()
            output_file.write(content + "\n")
print(f"Combined SQL file created at: {output_file_path}")
