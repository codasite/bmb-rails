import { useContext, useEffect } from 'react'
import { PickableBracket } from '../../../shared/components/Bracket'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../../shared/context/context'
import { ProfilePicture } from '../../../shared/components/ProfilePicture'
import { PlayRes } from '../../../shared/api/types/bracket'
import { WithMatchTree2 } from '../../../shared/components/HigherOrder/WithMatchTree'
import { WindowDimensionsContext } from '../../../shared/context/WindowDimensionsContext'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { ScoredPlayBracket } from '../../../shared/components/Bracket/ScoredPlayBracket'
import { loadMostPopularPicks, loadPlay, loadPlayMeta } from '../../../shared'
import { BracketResultsStatusTag } from '../../BracketResultsStatusTag'
import { getVotingPlayTrees } from './getVotingPlayTrees'

const ViewVotingPlay = (props: {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  bracketPlay: PlayRes
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
}) => {
  const { bracketPlay: play } = props

  const { width: windowWidth } = useContext(WindowDimensionsContext)

  const { playTree, setPlayTree, setMostPopularPicksTree } =
    getVotingPlayTrees()

  useEffect(() => {
    loadPlayMeta(play, props.setBracketMeta)
    loadPlay(play, setPlayTree)
    loadMostPopularPicks(play?.bracket, setMostPopularPicksTree)
  }, [play])

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover ${
        props.darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${
          props.darkMode ? darkBracketBg : lightBracketBg
        })`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-[900px] tw-m-auto tw-pb-[83px] tw-pt-[62px] tw-px-20`}
      >
        {playTree && (
          <>
            <ScaledBracket
              BracketComponent={PickableBracket}
              matchTree={playTree}
              windowWidth={windowWidth}
              paddingX={20}
            />
            <div className="tw-w-full tw-mt-40">
              {/* <ViewPlayPageButtons play={play} /> */}
            </div>
          </>
        )}
      </div>
    </div>
  )
}

export default WithMatchTree2(ViewVotingPlay)
