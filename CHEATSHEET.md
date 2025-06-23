# Neovim keybindings reference

## Global shortcuts
### Tabs workflow
- `gt`/`gT`: switch tabs forward and backwards, respectively
- `{n}gt`: switch to the nth tab

### Selection
- `vap`: selects the current block

### Page scrolling
- `<C-e>`: scrolls down one line (or n lines)
- `<C-d>`: scrolls down 1/2 window
- `<C-f>`: scrolls down 1 window
- `<C-y>`: scrolls up one line (or n lines)
- `<C-u>`: scrolls up 1/2 window
- `<C-b>`: scrolls up 1 window
- `{n}zt`: goes to nth line

### Text replacement and insertion
- `a`: skips one char entering the insert mode
- `o`: adds one blank line below entering the insert mode
- `C`: deletes the whole line entering the insert mode
- `R`: enters replacement mode - whatever you type replaces the text under the cursor

## Global commands
- `:tabedit /path/to/file`: opens the desired file in a new tab (same as :tabe)

## Plugin shortcuts
### Harpoon
- `<leader>a`: adds the file to the harpoon list
- `<leader>r`: removes the file from the harpoon list
- `<leader>h`: toggles the harpoon list
- `<leader>[n]`: goes to the nth item of the list (max: 8)
- `<C-n>`: goes to the next list's item
- `<C-p>`: goes to the previous list's item

### Telescope
- `<leader>ff`: find files
- `<leader>fg`: live grep

### nvim-tree
Plugin for navigating through files

Check [this documentation](https://docs.rockylinux.org/books/nvchad/nvchad_ui/nvimtree/) for more.

- `<leader>b`: toggles the file explorer (customized)
- `a`: creates a new file or directory
- `r`: rename file
- `d`: delete
- `x`: cut
- `c`: copy
- `p`: paste
- `y`: copies the filename
- `Y`: copies the relative path of the file
- `g + y`: copies the absolute path of the file
- `E`: expands every folder
- `W`: collapses every folder
- `<C-t>`: opens the file under the cursor in a new tab
- `<C-v>`: opens the file under the cursor dividing it vertically in two parts
- `<C-h>`: opens the file under the cursor dividing it horizontally in two parts
- `<C-k>`: displays a brief info about the file
- `-`: goes up one dir (as root directory)
- `<C-]>`: goes down one dir (as root directory)

### Toggleterm
Plugin for opening a terminal instance inside neovim
- `<C-\>`: toggles the terminal
