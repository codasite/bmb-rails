import { useEffect, useState } from 'react'
import { WpbbAppObj } from '../../../utils/WpbbAjax'
import { CompleteRoundModal } from './CompleteRoundModal'
import { DeleteBracketModal } from './DeleteBracketModal'
import { EditBracketModal } from './EditBracketModal'
import { LockLiveTournamentModal } from './LockLiveTournamentModal'
import { PublishBracketModal } from './PublishBracketModal'
import { SetTournamentFeeModal } from './SetTournamentFeeModal'
import { ShareBracketModal } from './ShareBracketModal'
import { UpcomingNotificationModal } from './UpcomingNotificationModal'
import { MoreOptionsModal } from './MoreOptionsModal'
import { BracketData } from './BracketData'
import { TournamentModalVisibility } from './TournamentModalVisibility'

export const TournamentModals = (props: { appObj: WpbbAppObj }) => {
  const [modalVisibility, setModalVisibility] =
    useState<TournamentModalVisibility>({
      editBracket: false,
      shareBracket: false,
      deleteBracket: false,
      setTournamentFee: false,
      lockLiveTournament: false,
      moreOptions: false,
    })
  const [bracketData, setBracketData] = useState<BracketData>({})

  const setShowModal = (
    modalName?: keyof TournamentModalVisibility,
    show?: boolean
  ) => {
    setModalVisibility({
      editBracket: false,
      shareBracket: false,
      deleteBracket: false,
      setTournamentFee: false,
      lockLiveTournament: false,
      moreOptions: false,
      [modalName]: show,
    })
  }

  useEffect(() => {
    console.log('bracketData', bracketData)
  }, [bracketData])

  return (
    <>
      <EditBracketModal
        show={modalVisibility.editBracket}
        setShow={(show) => setShowModal('editBracket', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <SetTournamentFeeModal
        applicationFeeMinimum={props.appObj.applicationFeeMinimum}
        applicationFeePercentage={props.appObj.applicationFeePercentage}
        show={modalVisibility.setTournamentFee}
        setShow={(show) => setShowModal('setTournamentFee', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <ShareBracketModal
        show={modalVisibility.shareBracket}
        setShow={(show) => setShowModal('shareBracket', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <LockLiveTournamentModal
        show={modalVisibility.lockLiveTournament}
        setShow={(show) => setShowModal('lockLiveTournament', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <DeleteBracketModal
        show={modalVisibility.deleteBracket}
        setShow={(show) => setShowModal('deleteBracket', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <PublishBracketModal
        upgradeAccountUrl={props.appObj.upgradeAccountUrl}
        canCreateBracket={props.appObj.userCanShareBracket}
      />
      <UpcomingNotificationModal
        isUserLoggedIn={props.appObj.isUserLoggedIn}
        loginUrl={props.appObj.loginUrl}
      />
      <CompleteRoundModal />
      <MoreOptionsModal
        show={modalVisibility.moreOptions}
        setShow={(show) => setShowModal('moreOptions', show)}
        showModal={(modalName) => setShowModal(modalName, true)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
    </>
  )
}
