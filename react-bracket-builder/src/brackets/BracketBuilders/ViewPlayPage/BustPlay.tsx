import { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { getBracketMeta } from '../../shared/components/Bracket/utils'
import { ViewPlayPageProps } from './types'
import { WithMatchTree3 } from '../../shared/components/HigherOrder'
import { getBustTrees } from '../BustPlayPage/utils'
import { BustPlayView } from '../BustPlayPage/BustPlayView'

const BustPlay = (props: ViewPlayPageProps) => {
  const { setBracketMeta, bracketPlay: play } = props

  const [busteeDisplayName, setBusteeDisplayName] = useState<string>()
  const [busteeThumbnail, setBusteeThumbnail] = useState<string>()
  const { busterTree, busteeTree, setBusterTree, setBusteeTree } =
    getBustTrees()

  useEffect(() => {
    handleBracketMeta()
    buildMatchTrees()
  }, [])

  const handleBracketMeta = () => {
    const bracket = play.bracket
    const busteeName = play.bustedPlay?.authorDisplayName
    const busteeThumbnail = play.bustedPlay?.thumbnailUrl
    const busterName = play.authorDisplayName
    const bracketTitle = bracket?.title || 'Bracket'
    const title = `${busterName}'s Bust of ${busterName}'s ${bracketTitle} Picks`
    const { date } = getBracketMeta(bracket)
    setBracketMeta({ title, date })
    setBusteeDisplayName(busteeName)
    setBusteeThumbnail(busteeThumbnail)
  }

  const buildMatchTrees = () => {
    const bracket = play.bracket
    const matches = bracket?.matches
    const busterPicks = play.picks
    const busteePicks = play.bustedPlay?.picks
    const numTeams = bracket?.numTeams
    const busterTree = MatchTree.fromPicks(numTeams, matches, busterPicks)
    console.log('busterTree', busterTree)
    const busteeTree = MatchTree.fromPicks(numTeams, matches, busteePicks)
    console.log('busteeTree', busteeTree)
    setBusterTree(busterTree)
    setBusteeTree(busteeTree)
  }

  const handleBustAgain = async () => {
    window.location.href = play?.url + '/bust'
  }
  return (
    <BustPlayView
      bracket={play.bracket}
      busterTree={busterTree}
      busteeDisplayName={busteeDisplayName}
      busteeThumbnail={busteeThumbnail}
      onButtonClick={handleBustAgain}
      buttonText="Bust Again"
    />
  )
}

const Wrapped = WithMatchTree3(BustPlay)
export { Wrapped as BustPlay }
