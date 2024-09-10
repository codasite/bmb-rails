import { useContext, useEffect } from 'react'
import { DefaultBracket } from '../../../brackets/shared/components/Bracket'
import darkBracketBg from '../../../brackets/shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../../brackets/shared/assets/bracket-bg-light.png'
import {
  BracketMeta,
  DarkModeContext,
} from '../../../brackets/shared/context/context'
import { PlayRes } from '../../../brackets/shared/api/types/bracket'
import { WithMatchTree2 } from '../../../brackets/shared/components/HigherOrder/WithMatchTree'
import { WindowDimensionsContext } from '../../../brackets/shared/context/WindowDimensionsContext'
import { ScaledBracket } from '../../../brackets/shared/components/Bracket/ScaledBracket'
import {
  loadMostPopularPicks,
  loadPlay,
  loadPlayMeta,
} from '../../../brackets/shared'
import { useVotingPlayTrees } from './getVotingPlayTrees'
import { VotingPlayTeamSlot } from './VotingPlayTeamSlot'

const ViewVotingPlay = (props: {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  bracketPlay: PlayRes
}) => {
  const { darkMode } = useContext(DarkModeContext)
  const { bracketPlay: play } = props

  const { width: windowWidth } = useContext(WindowDimensionsContext)

  const { playTree, setPlayTree, setMostPopularPicksTree } =
    useVotingPlayTrees()

  useEffect(() => {
    loadPlayMeta(play, props.setBracketMeta)
    loadPlay(play, setPlayTree)
    loadMostPopularPicks(play?.bracket, setMostPopularPicksTree)
  }, [play])

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover ${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-[900px] tw-m-auto tw-pb-[83px] tw-pt-[62px] tw-px-20`}
      >
        {playTree && (
          <>
            <ScaledBracket
              BracketComponent={DefaultBracket}
              TeamSlotComponent={VotingPlayTeamSlot}
              matchTree={playTree}
              windowWidth={windowWidth}
              paddingX={20}
            />
          </>
        )}
      </div>
    </div>
  )
}

export default WithMatchTree2(ViewVotingPlay)
