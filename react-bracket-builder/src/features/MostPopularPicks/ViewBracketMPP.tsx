import { useContext, useEffect } from 'react'
import {
  BracketRes,
  loadMostPopularPicks,
  loadPlay,
  loadPlayMeta,
  PlayRes,
} from '../../brackets/shared'
import { BracketMeta } from '../../brackets/shared/context/context'
import { WindowDimensionsContext } from '../../brackets/shared/context/WindowDimensionsContext'
import { MatchTree } from '../../brackets/shared/models/MatchTree'
import { getBracketMeta } from '../../brackets/shared/components/Bracket/utils'
import darkBracketBg from '../../brackets/shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../brackets/shared/assets/bracket-bg-light.png'
import { ProfilePicture } from '../../brackets/shared/components/ProfilePicture'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithWindowDimensions,
} from '../../brackets/shared/components/HigherOrder'
import { ScaledBracket } from '../../brackets/shared/components/Bracket/ScaledBracket'
import { MPPBracket } from '../../brackets/shared/components/Bracket'
import { BracketHeaderTag } from '../../brackets/BracketBuilders/BracketHeaderTag'

export const ViewBracketMPP = (props: {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  bracket?: BracketRes
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
}) => {
  const { bracket } = props
  useEffect(() => {
    if (bracket && !props.matchTree) {
      props.setBracketMeta(getBracketMeta(bracket))
      loadMostPopularPicks(bracket, props.setMatchTree)
    }
  }, [bracket])

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
        {props.matchTree && (
          <>
            <div
              className={`tw-flex tw-flex-col tw-gap-30 tw-w-full tw-mb-20 sm:tw-mb-10 `}
            >
              {bracket?.thumbnailUrl && (
                <div className="tw-flex tw-justify-center">
                  <ProfilePicture
                    src={bracket?.thumbnailUrl}
                    alt="bracket-image"
                    color="blue"
                    shadow={false}
                  />
                </div>
              )}
              <div className="tw-self-center">
                <BracketHeaderTag text="Most Popular Picks" color="yellow" />
              </div>
            </div>
            <ScaledBracket
              BracketComponent={MPPBracket}
              matchTree={props.matchTree}
              paddingX={20}
            />
          </>
        )}
      </div>
    </div>
  )
}

const Wrapped = WithWindowDimensions(
  WithDarkMode(WithMatchTree(WithBracketMeta(ViewBracketMPP)))
)
export default Wrapped
