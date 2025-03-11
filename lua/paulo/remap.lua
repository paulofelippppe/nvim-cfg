vim.keymap.set("n", "<leader>b", ":NvimTreeToggle<CR>")
vim.keymap.set("n", "<leader>tc", ":tabclose<CR>")

-- Move the selected text upward or downward
vim.keymap.set("v", "J", ":m '>+1<CR>gv=gv")
vim.keymap.set("v", "K", ":m '<-2<CR>gv=gv")

-- Exit terminal mode
vim.keymap.set("t", "<Esc>", "<C-\\><C-n>", { noremap = true })
