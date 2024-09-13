import { useContext, useEffect, useState } from 'react'
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
  loadBracketResults,
  loadPlay,
  loadPlayMeta,
} from '../../../brackets/shared'
import { useVotingPlayTrees } from './useVotingPlayTrees'
import { VotingPlayTeamSlot } from './VotingPlayTeamSlot'
import { VotingResultsTeamSlot } from '../VotingResultsTeamSlot'
import ToggleSwitch from '../../../ui/ToggleSwitch'

const ViewVotingPlay = (props: {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  bracketPlay: PlayRes
}) => {
  const { darkMode } = useContext(DarkModeContext)
  const { bracketPlay: play } = props

  const { width: windowWidth } = useContext(WindowDimensionsContext)

  const { playTree, setPlayTree, setBracketResultsTree, bracketResultsTree } =
    useVotingPlayTrees()

  // Whether to show my picks vs show popular picks
  const [showPopularPicks, setShowPopularPicks] = useState(false)
  const handleToggleShowPopularPicks = () => {
    setShowPopularPicks(!showPopularPicks)
  }

  useEffect(() => {
    loadPlayMeta(play, props.setBracketMeta)
    loadPlay(play, setPlayTree)
    loadBracketResults(play?.bracket, setBracketResultsTree)
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
        <div className="tw-mb-20 tw-flex tw-items-center tw-gap-14">
          <span className="tw-font-500">My Picks</span>
          <ToggleSwitch
            isOn={!showPopularPicks}
            handleToggle={handleToggleShowPopularPicks}
            color={showPopularPicks ? 'green' : 'white'}
            title={showPopularPicks ? 'Show my picks' : 'Show popular picks'}
          />
          <span className="tw-font-500">Popular Picks</span>
        </div>
        {playTree && (
          <>
            <ScaledBracket
              BracketComponent={DefaultBracket}
              TeamSlotComponent={
                showPopularPicks ? VotingResultsTeamSlot : VotingPlayTeamSlot
              }
              matchTree={bracketResultsTree}
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
