import os
import tkinter as tk
from tkinter import filedialog

# daftar folder/file yang mau di-skip
SKIP_DIRS = {".git", "__pycache__", "node_modules"}
SKIP_FILES = {".DS_Store", "Thumbs.db"}

def generate_tree(dir_path, prefix=""):
    tree_str = ""
    items = os.listdir(dir_path)
    items.sort()

    for i, item in enumerate(items):
        if item in SKIP_DIRS or item in SKIP_FILES:
            continue  # skip folder/file tertentu

        path = os.path.join(dir_path, item)
        connector = "└─" if i == len(items) - 1 else "├─"

        if os.path.isdir(path):
            tree_str += f"{prefix}{connector}▮ {item}\n"
            extension = "   " if i == len(items) - 1 else "│  "
            tree_str += generate_tree(path, prefix + extension)
        else:
            tree_str += f"{prefix}{connector}▯ {item}\n"

    return tree_str


def save_tree_to_txt(root_dir, output_file="struktur_proyek.txt"):
    tree_structure = f"▮ {os.path.basename(root_dir)}\n"
    tree_structure += generate_tree(root_dir)
    
    with open(output_file, "w", encoding="utf-8") as f:
        f.write(tree_structure)

    print(f"Struktur proyek berhasil disimpan di {output_file}")


def pilih_folder():
    root = tk.Tk()
    root.withdraw()  # sembunyikan window utama
    folder = filedialog.askdirectory(title="Pilih Folder Proyek")
    return folder


if __name__ == "__main__":
    project_path = pilih_folder()
    if project_path:
        save_tree_to_txt(project_path)
    else:
        print("Tidak ada folder yang dipilih.")
