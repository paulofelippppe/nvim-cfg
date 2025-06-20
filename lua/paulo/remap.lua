vim.keymap.set("n", "<leader>b", ":NvimTreeToggle<CR>")
vim.keymap.set("n", "<leader>tc", ":tabclose<CR>")

-- Move the selected text upward or downward
vim.keymap.set("v", "J", ":m '>+1<CR>gv=gv")
vim.keymap.set("v", "K", ":m '<-2<CR>gv=gv")

-- Exit terminal mode
vim.keymap.set("t", "<Esc>", "<C-\\><C-n>", { noremap = true })

-- Harpoon keybindings
--vim.keymap.set("n", "<leader>hr", ":lua require('harpoon').list().remove()<cr>")
--vim.keymap.set("n", "<leader>ha", ":lua require('harpoon.mark').add_file()<Cr>")
--vim.keymap.set("n", "<leader>ht", ":lua require('harpoon.ui').toggle_quick_menu()<Cr>")
--for i=1,8 do
--    vim.keymap.set("n", string.format("<leader>%d", i), string.format(":lua require('harpoon.ui').nav_file(%d)<Cr>", i))
--end
