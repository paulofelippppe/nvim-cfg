return {
    "ThePrimeagen/harpoon",
    branch = "harpoon2",
    dependencies = { "nvim-lua/plenary.nvim" },
    config = function()
        local harpoon = require'harpoon'
        harpoon:setup()

        vim.keymap.set("n", "<leader>a", function() harpoon:list():add() end)
        vim.keymap.set("n", "<leader>r", function() harpoon:list():remove() end)
        vim.keymap.set("n", "<leader>h", function() harpoon.ui:toggle_quick_menu(harpoon:list()) end)

        for i=1,8 do
            vim.keymap.set("n", string.format("<leader>%d", i), function() harpoon:list():select(i) end)
        end

        -- Toggle previous & next buffers stored within Harpoon list
        vim.keymap.set("n", "<C-n>", function() harpoon:list():next() end)
        vim.keymap.set("n", "<C-p>", function() harpoon:list():prev() end)
    end,
}
