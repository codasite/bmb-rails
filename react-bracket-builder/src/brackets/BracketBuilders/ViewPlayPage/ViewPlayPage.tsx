import { useContext, useEffect } from 'react'
import { PickableBracket } from '../../shared/components/Bracket'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context/context'
import { ProfilePicture } from '../../shared/components/ProfilePicture'
import { PlayRes } from '../../shared/api/types/bracket'
import { WithMatchTree2 } from '../../shared/components/HigherOrder/WithMatchTree'
import { ViewPlayPageButtons } from './ViewPlayPageButtons'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { ScoredPlayBracket } from '../../shared/components/Bracket/ScoredPlayBracket'
import { loadBracketResults, loadPlay, loadPlayMeta } from '../../shared'
import { getScoredPlayTrees } from './getScoredPlayTrees'
import { BracketResultsStatusTag } from '../BracketResultsStatusTag'

const PlayScore = (props: { scorePercent: number; label: string }) => {
  return (
    <div className="tw-flex tw-flex-col tw-items-end tw-gap-4 tw-leading-none">
      <span className="tw-text-10 sm:tw-text-12 tw-font-700 tw-text-green">
        {props.label}
      </span>
      <span className="tw-text-36 sm:tw-text-48 tw-font-700 tw-text-green">
        {Math.round(props.scorePercent)}%
      </span>
    </div>
  )
}

const PlayRank = () => {
  return <div></div>
}

const ViewPlayPage = (props: {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  bracketPlay: PlayRes
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
}) => {
  const { bracketPlay: play } = props

  const { width: windowWidth } = useContext(WindowDimensionsContext)

  const isScored = play?.accuracyScore !== undefined
  const showProfilePicture = !!play?.thumbnailUrl

  const { playTree, setPlayTree, setResultsTree } = getScoredPlayTrees()

  useEffect(() => {
    loadPlayMeta(play, props.setBracketMeta)
    loadPlay(play, setPlayTree)
    loadBracketResults(play?.bracket, setResultsTree)
  }, [play])

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover ${props.darkMode ? ' tw-dark' : ''
        }`}
      style={{
        backgroundImage: `url(${props.darkMode ? darkBracketBg : lightBracketBg
          })`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-[900px] tw-m-auto tw-pb-[83px] tw-pt-[62px] tw-px-20`}
      >
        {playTree && (
          <>
            <div
              className={`tw-flex tw-flex-col tw-w-full ${isScored ? 'tw-mb-10' : 'tw-mb-40'
                } ${showProfilePicture ? 'tw-gap-30' : 'tw-gap-10'}`}
            >
              <div className="tw-flex tw-items-center">
                <div className="tw-basis-1/3">
                  <PlayRank />
                </div>
                <div className="tw-flex tw-justify-center tw-basis-1/3">
                  {showProfilePicture && (
                    <ProfilePicture
                      src={play.thumbnailUrl}
                      alt="celebrity-photo"
                      color="blue"
                      shadow={false}
                    />
                  )}
                </div>
                <div className="tw-basis-1/3">
                  {isScored && (
                    <PlayScore
                      scorePercent={play?.accuracyScore * 100}
                      label={
                        play?.bracket?.status === 'complete'
                          ? 'Your Score'
                          : 'Current Score'
                      }
                    />
                  )}
                </div>
              </div>
              {isScored && (
                <div className="tw-self-center">
                  <BracketResultsStatusTag bracket={play?.bracket} />
                </div>
              )}
            </div>
            <ScaledBracket
              BracketComponent={isScored ? ScoredPlayBracket : PickableBracket}
              matchTree={playTree}
              windowWidth={windowWidth}
              paddingX={20}
            />
            <div className="tw-w-full tw-mt-40">
              <ViewPlayPageButtons play={play} />
            </div>
          </>
        )}
      </div>
    </div>
  )
}

export default WithMatchTree2(ViewPlayPage)
