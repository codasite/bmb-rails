import React, { useEffect, useState } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignoredododo
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context/context'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { ViewPlayPageProps } from './types'
import { FullBracketPage } from '../PaginatedPlayBuilder/FullBracketPage'
import { useWindowDimensions } from '../../../utils/hooks'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { Spinner } from '../../shared/components/Spinner'
import { addToApparelHandler } from './utils'

export const BracketPlayPage = (props: ViewPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    redirectUrl,
  } = props

  const { width: windowWidth, height: windowHeight } = useWindowDimensions()
  const [processing, setProcessing] = useState(false)
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(play?.bracket?.numTeams))

  useEffect(() => {
    const picks = play?.picks
    const meta = getBracketMeta(play?.bracket)
    setBracketMeta(meta)
    const bracket = play?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    if (picks && matches) {
      const tree = MatchTree.fromPicks(numTeams, matches, picks)
      if (tree) {
        setMatchTree(tree)
      }
    }
  }, [play])

  const handleAddToApparel = async () => {
    addToApparelHandler(play?.id, redirectUrl)
  }

  if (showPaginated) {
    return (
      <FullBracketPage
        onApparelClick={handleAddToApparel}
        matchTree={matchTree}
        darkMode={darkMode}
        setDarkMode={setDarkMode}
      />
    )
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
      >
        {matchTree && (
          <>
            <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
            </div>
            <PickableBracket matchTree={matchTree} />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="green"
                size="big"
                darkMode={darkMode}
                onClick={handleAddToApparel}
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
