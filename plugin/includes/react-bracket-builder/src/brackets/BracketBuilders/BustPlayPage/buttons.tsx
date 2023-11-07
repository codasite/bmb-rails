import {
  ActionButton,
  ActionButtonProps,
} from '../../shared/components/ActionButtons'
import { ReactComponent as LightningIcon } from '../../shared/assets/lightning.svg'
import { ReactComponent as PlayIcon } from '../../shared/assets/play.svg'
import { ReactComponent as PlusIcon } from '../../shared/assets/plus.svg'

interface BustablePlayPageButtonsProps {
  handleBustPlay: () => void
  handlePlayBracket: () => void
  handleAddApparel: () => void
  size?: string
}
export const BustablePlayPageButtons = (
  props: BustablePlayPageButtonsProps
) => {
  const { handleBustPlay, handlePlayBracket, handleAddApparel, size } = props
  const iconHeight = size === 'small' ? 20 : 24
  const paddingY = 15
  const fontSize = size === 'small' ? 16 : 24
  const fontWeight = 700
  const borderWidth = 1
  return (
    <div className="tw-flex tw-flex-col tw-justify-center tw-gap-15 tw-self-stretch tw-max-w-[900px] tw-w-full tw-mx-auto">
      <ActionButton
        onClick={handleAddApparel}
        backgroundColor="grey-blue"
        paddingY={paddingY}
        textColor="white"
        borderRadius={8}
        fontSize={fontSize}
        fontWeight={fontWeight}
        borderWidth={borderWidth}
      >
        <PlusIcon style={{ height: iconHeight }} />
        Add to Apparel
      </ActionButton>
      <div
        className={`tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-gap-15 md:tw-mt-30 `}
      >
        <ActionButton
          onClick={handlePlayBracket}
          variant="green"
          paddingY={paddingY}
          fontSize={fontSize}
          fontWeight={fontWeight}
          borderWidth={borderWidth}
          className="tw-flex-grow tw-basis-1/2"
        >
          <PlayIcon style={{ height: iconHeight }} />
          Play Bracket
        </ActionButton>
        <ActionButton
          onClick={handleBustPlay}
          variant="red"
          paddingY={paddingY}
          fontSize={fontSize}
          fontWeight={fontWeight}
          borderWidth={borderWidth}
          className="tw-flex-grow tw-basis-1/2"
        >
          <LightningIcon style={{ height: iconHeight }} />
          Bust Picks
        </ActionButton>
      </div>
    </div>
  )
}
