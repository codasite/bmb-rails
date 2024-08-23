import React, { useContext, useEffect } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta, DarkModeContext } from '../../shared/context/context'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import {
  WithBracketMeta,
  WithMatchTree,
  WithWindowDimensions,
} from '../../shared/components/HigherOrder'
import { getBracketMeta } from '../../shared/components/Bracket/utils'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'
import { ResultsBracket } from '../../shared/components/Bracket'
import { ProfilePicture } from '../../shared/components/ProfilePicture'
import { ViewResultsPageButtons } from './ViewResultsPageButtons'
import { BracketRes, loadBracketResults } from '../../shared'
import { BracketResultsStatusTag } from '../BracketResultsStatusTag'

export const ViewBracketResultsPage = (props: {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  bracket?: BracketRes
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}) => {
  const { bracket } = props
  const { darkMode } = useContext(DarkModeContext)
  useEffect(() => {
    if (bracket && !props.matchTree) {
      props.setBracketMeta(getBracketMeta(bracket))
      loadBracketResults(bracket, props.setMatchTree)
    }
  }, [bracket])

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
                <BracketResultsStatusTag bracket={bracket} />
              </div>
            </div>
            <ScaledBracket
              BracketComponent={ResultsBracket}
              matchTree={props.matchTree}
              paddingX={20}
            />
            <div className="tw-w-full tw-mt-40">
              <ViewResultsPageButtons
                bracket={bracket}
                // addApparelUrl={props.addApparelUrl}
              />
            </div>
          </>
        )}
      </div>
    </div>
  )
}

const Wrapped = WithWindowDimensions(
  WithMatchTree(WithBracketMeta(ViewBracketResultsPage))
)
export default Wrapped
