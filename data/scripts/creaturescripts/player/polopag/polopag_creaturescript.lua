local pixModalCreatureEvent = CreatureEvent("ModalWindowPix")
local PixGenerator = dofile('data/libs/polopag/pix_class.lua')

function pixModalCreatureEvent.onModalWindow(player, modalWindowId, buttonId, choiceId)
    local pixGen = PixGenerator(player:getAccountId())
    pixGen:generateWindow(player, modalWindowId, buttonId, choiceId)
    return true
end

pixModalCreatureEvent:register()
