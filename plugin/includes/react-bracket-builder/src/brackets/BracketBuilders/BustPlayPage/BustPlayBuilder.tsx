import React, { useEffect } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithDarkMode,
  WithMatchTree,
  WithBracketMeta,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import redBracketBg from '../../shared/assets/bracket-bg-red.png'
//@ts-ignore
import { bracketApi } from '../../shared/api/bracketApi'
import { PlayRes } from '../../shared/api/types/bracket'

interface BustPlayBuilderProps {
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  bracketPlay: PlayRes
  redirectUrl: string
}

export const BustPlayBuilder = (props: BustPlayBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    bracketPlay: play,
    redirectUrl,
  } = props

  const handleSubmit = () => {
    // window.location.href = props.apparelUrl
    console.log('handleSubmit')
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark-mode`}
      style={{
        backgroundImage: `url(${redBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
      >
        {matchTree && (
          <>
            <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
            </div>
            <PickableBracket matchTree={matchTree} />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="big-green"
                darkMode={true}
                onClick={handleSubmit}
              >
                Add to Apparel
              </ActionButton>
            </div>
          </>
        )}
      </div>
    </div>
  )
}