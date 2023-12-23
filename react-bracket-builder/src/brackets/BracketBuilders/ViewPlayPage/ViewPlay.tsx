import { useContext, useEffect } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignoredododo
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { ViewPlayPageProps } from './types'
import { FullBracketPage } from '../PaginatedPlayBuilder/FullBracketPage'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { addToApparelHandler } from './utils'
import { WithMatchTree } from '../../shared/components/HigherOrder'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'
import { AddToApparel } from '../AddToApparel'

const ViewPlay = (props: ViewPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    addToApparelUrl,
  } = props

  const { width: windowWidth, height: windowHeight } = useContext(
    WindowDimensionsContext
  )
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
    await addToApparelHandler(play?.id, addToApparelUrl)
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
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-80`}
      >
        {matchTree && (
          <>
            <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
            </div>
            <PickableBracket matchTree={matchTree} />
            <div className="tw-px-24 tw-mt-60 tw-flex tw-flex-col tw-items-stretch tw-self-stretch">
              <AddToApparel
                handleApparelClick={handleAddToApparel}
                disabled={false}
              />
            </div>
          </>
        )}
      </div>
    </div>
  )
}

const WrappedViewPlay = WithMatchTree(ViewPlay)
export { WrappedViewPlay as ViewPlay }
