import React, { useState, useEffect } from 'react'
import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'
import Button from 'react-bootstrap/Button'
import ButtonGroup from 'react-bootstrap/ButtonGroup'
import './optionstree.css'

enum MatchSetValues {
    firstSetMinValue = 2,
    firstSetMaxValue = 16,
    secondSetMinValue = 18,
    secondSetMaxValue = 32,
    thirdSetMinValue = 34,
    thirdSetMaxValue = 64
}

const Options = () => {

    const [firstNum, setFirstNum] = useState(MatchSetValues.firstSetMaxValue)
    const [secondNum, setSecondNum] = useState(MatchSetValues.secondSetMaxValue)
    const [thirdNum, setThirdNum] = useState(MatchSetValues.thirdSetMaxValue)

    const [selectedBox, setSelectedBox] = useState(firstNum)

    const [isShowfirstMatchOperators, setShowfirstMatchOperators] = useState(true)
    const [isShowsecondMatchOperators, setShowsecondMatchOperators] = useState(false)
    const [isShowthirdMatchOperators, setShowthirdMatchOperators] = useState(false)

    const [disableFirstSetAdd, setdisableFirstSetAdd] = useState(true)
    const [disableFirstSetMinus, setdisableFirstSetMinus] = useState(false)

    const [disableSecondSetAdd, setdisableSecondSetAdd] = useState(true)
    const [disableSecondSetMinus, setdisableSecondSetMinus] = useState(false)

    const [disableThirdSetAdd, setdisableThirdSetAdd] = useState(true)
    const [disableThirdSetMinus, setdisableThirdSetMinus] = useState(false)

    const handleBoxClick = (currentValue) => {
        setSelectedBox(currentValue)
        switch (currentValue) {
            case firstNum:
                setOperators(true, false, false)
                setSecondNum(MatchSetValues.secondSetMaxValue)
                setThirdNum(MatchSetValues.thirdSetMaxValue)
                break
            case secondNum:
                setOperators(false, true, false)
                setFirstNum(MatchSetValues.firstSetMaxValue)
                setThirdNum(MatchSetValues.thirdSetMaxValue)
                break
            case thirdNum:
                setOperators(false, false, true)
                setFirstNum(MatchSetValues.firstSetMaxValue)
                setSecondNum(MatchSetValues.secondSetMaxValue)
                break
        }
    }

    const setOperators = (showFirst, showSecond, showThird) => {
        setShowfirstMatchOperators(showFirst)
        setShowsecondMatchOperators(showSecond)
        setShowthirdMatchOperators(showThird)
    }

    useEffect(() => {
        if (firstNum === MatchSetValues.firstSetMaxValue) {
            setdisableFirstSetAdd(true)
        }
        else {
            setdisableFirstSetAdd(false)
        }
        if (firstNum === MatchSetValues.firstSetMinValue) {
            setdisableFirstSetMinus(true)
        }
        else {
            setdisableFirstSetMinus(false)
        }
    }, [firstNum])

    useEffect(() => {
        if (secondNum === MatchSetValues.secondSetMaxValue) {
            setdisableSecondSetAdd(true)
        }
        else {
            setdisableSecondSetAdd(false)
        }
        if (secondNum === MatchSetValues.secondSetMinValue) {
            setdisableSecondSetMinus(true)
        }
        else {
            setdisableSecondSetMinus(false)
        }
    }, [secondNum])

    useEffect(() => {
        if (thirdNum === MatchSetValues.thirdSetMaxValue) {
            setdisableThirdSetAdd(true)
        }
        else {
            setdisableThirdSetAdd(false)
        }
        if (thirdNum === MatchSetValues.thirdSetMinValue) {
            setdisableThirdSetMinus(true)
        }
        else {
            setdisableThirdSetMinus(false)
        }
    }, [thirdNum])

    const handleOperation = (op) => {
        let a = performOperation(op)
        setSelectedBox(a)
    }

    const performOperation = (op) => {
        if (selectedBox !== null) {
            switch (op) {
                case '+':
                    if (selectedBox === firstNum) {
                        setFirstNum(firstNum + 2)
                        return firstNum + 2
                    } else if (selectedBox === secondNum) {
                        setSecondNum(secondNum + 2)
                        return secondNum + 2
                    } else if (selectedBox === thirdNum) {
                        setThirdNum(thirdNum + 2)
                        return thirdNum + 2
                    }
                    break
                case '-':
                    if (selectedBox === firstNum) {
                        setFirstNum(firstNum - 2)
                        return firstNum - 2
                    } else if (selectedBox === secondNum) {
                        setSecondNum(secondNum - 2)
                        return secondNum - 2
                    } else if (selectedBox === thirdNum) {
                        setThirdNum(thirdNum - 2)
                        return thirdNum - 2
                    }
                    break
                default:
                    break
            }
        }

        return selectedBox // Return selectedBox if no valid operation is performed
    }

    interface ButtonProps {
        isAddDisable: boolean
        isSubstractDisabled: boolean
    }

    const CreateButtons = (props: ButtonProps) => {
        const { isAddDisable, isSubstractDisabled } = props
        return (
            <div>
                <ButtonGroup aria-label="Basic example">
                    <Button disabled={isAddDisable} variant='secondary' onClick={() => handleOperation('+')}>+</Button>
                    <Button disabled={isSubstractDisabled} variant='secondary' onClick={() => handleOperation('-')}>-</Button>
                </ButtonGroup>
            </div>
        )

    }

    return (
        <div className={'options-page'}>
            <div className={'options-bracket-display'}>
                <div className={'randomize-teams'}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.2929 2.79289C17.6834 2.40237 18.3166 2.40237 18.7071 2.79289L21.7071 5.79289C22.0976 6.18342 22.0976 6.81658 21.7071 7.20711L18.7071 10.2071C18.3166 10.5976 17.6834 10.5976 17.2929 10.2071C16.9024 9.81658 16.9024 9.18342 17.2929 8.79289L18.5858 7.5H18.5689C17.5674 7.5 17.275 7.51019 17.0244 7.5863C16.7728 7.6627 16.5388 7.78796 16.3356 7.95491C16.1333 8.12123 15.9626 8.35886 15.4071 9.19214L10.257 16.9173C10.2323 16.9543 10.2079 16.9909 10.1839 17.027C9.73463 17.7018 9.39557 18.2111 8.93428 18.5902C8.52804 18.9241 8.05995 19.1746 7.55679 19.3274C6.98546 19.5009 6.37366 19.5005 5.56299 19.5001C5.51961 19.5 5.47566 19.5 5.43112 19.5H3C2.44772 19.5 2 19.0523 2 18.5C2 17.9477 2.44772 17.5 3 17.5H5.43112C6.4326 17.5 6.72499 17.4898 6.97562 17.4137C7.2272 17.3373 7.46125 17.212 7.66437 17.0451C7.86672 16.8788 8.03739 16.6411 8.59291 15.8079L13.743 8.08274C13.7677 8.04568 13.792 8.0091 13.8161 7.973C14.2654 7.29821 14.6044 6.78895 15.0657 6.40982C15.472 6.07592 15.9401 5.82541 16.4432 5.6726C17.0145 5.4991 17.6263 5.49946 18.437 5.49995C18.4804 5.49997 18.5243 5.5 18.5689 5.5H18.5858L17.2929 4.20711C16.9024 3.81658 16.9024 3.18342 17.2929 2.79289ZM6.97562 7.5863C6.72499 7.51019 6.4326 7.5 5.43112 7.5H3C2.44772 7.5 2 7.05228 2 6.5C2 5.94772 2.44772 5.5 3 5.5H5.43112C5.47566 5.5 5.51961 5.49997 5.56298 5.49995C6.37365 5.49946 6.98546 5.4991 7.55679 5.6726C8.05995 5.82541 8.52804 6.07592 8.93429 6.40982C9.39557 6.78894 9.73463 7.2982 10.1839 7.97299C10.2079 8.0091 10.2323 8.04568 10.257 8.08274L10.4987 8.4453C10.8051 8.90483 10.6809 9.5257 10.2214 9.83205C9.76184 10.1384 9.14097 10.0142 8.83462 9.5547L8.59291 9.19214C8.03739 8.35886 7.86672 8.12123 7.66437 7.95491C7.46125 7.78796 7.2272 7.6627 6.97562 7.5863ZM17.2929 14.7929C17.6834 14.4024 18.3166 14.4024 18.7071 14.7929L21.7071 17.7929C22.0976 18.1834 22.0976 18.8166 21.7071 19.2071L18.7071 22.2071C18.3166 22.5976 17.6834 22.5976 17.2929 22.2071C16.9024 21.8166 16.9024 21.1834 17.2929 20.7929L18.5858 19.5H18.5689C18.5243 19.5 18.4804 19.5 18.437 19.5001C17.6263 19.5005 17.0145 19.5009 16.4432 19.3274C15.9401 19.1746 15.472 18.9241 15.0657 18.5902C14.6044 18.2111 14.2654 17.7018 13.8161 17.027C13.7921 16.9909 13.7677 16.9543 13.743 16.9173L13.5013 16.5547C13.1949 16.0952 13.3191 15.4743 13.7786 15.1679C14.2382 14.8616 14.859 14.9858 15.1654 15.4453L15.4071 15.8079C15.9626 16.6411 16.1333 16.8788 16.3356 17.0451C16.5388 17.212 16.7728 17.3373 17.0244 17.4137C17.275 17.4898 17.5674 17.5 18.5689 17.5H18.5858L17.2929 16.2071C16.9024 15.8166 16.9024 15.1834 17.2929 14.7929Z" fill="white" />
                    </svg><span className={'randomize-teams-text'}>scramble team order</span>
                </div>
                <div className={'options-bracket-display-text'}>
                    Bracket style
                </div>
            </div>
            <div className={'options-bracket-tree'}>
                <div className={'options-bracket-dual-tree'}>
                    Dual Tree
                </div>
                <div className={'options-bracket-single-tree'}>
                    Single Tree
                </div>
            </div>
            <div className={'how-many-teams'}>
                <div className={'how-many-teams-text'}>
                    How Many total teams in Your Bracket
                </div>
            </div>
            <div className="d-flex justify-content-center">
                <Container>
                    <Row className="justify-content-center">
                        <div className="custom-col-container">
                            {[firstNum, secondNum, thirdNum].map((num) => (
                                <div
                                    key={num}
                                    className={`custom-col ${selectedBox === num ? 'highlight' : ''}`}
                                    onClick={() => handleBoxClick(num)}
                                >
                                    {num}
                                </div>
                            ))}
                        </div>
                    </Row>
                </Container>
            </div>
            <div>
                <Container>
                    <Row>
                        <Col>
                            <div style={{ visibility: isShowfirstMatchOperators ? 'visible' : 'hidden' }}>
                                <CreateButtons isAddDisable={disableFirstSetAdd} isSubstractDisabled={disableFirstSetMinus} />
                            </div>
                        </Col>
                        <Col>
                            <div style={{ visibility: isShowsecondMatchOperators ? 'visible' : 'hidden' }}>
                                <CreateButtons isAddDisable={disableSecondSetAdd} isSubstractDisabled={disableSecondSetMinus} />
                            </div>
                        </Col>
                        <Col>
                            <div style={{ visibility: isShowthirdMatchOperators ? 'visible' : 'hidden' }}>
                                <CreateButtons isAddDisable={disableThirdSetAdd} isSubstractDisabled={disableThirdSetMinus} />
                            </div>
                        </Col>
                    </Row>
                </Container>
            </div>
        </div>
    )
}

export default Options